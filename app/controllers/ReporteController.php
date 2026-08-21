<?php
class ReporteController extends Controller {

    private $reporteModel;
    const PW = 277; // A4 landscape usable mm

    public function __construct() {
        if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
            header('Location: ' . URL_ROOT . '/usuarios/login');
            exit();
        }
        $this->reporteModel = $this->model('ReporteModel');
    }

    public function index() {
        $this->view('admin/reportes', ['titulo' => 'Generar Reportes - Admin']);
    }

    /**
     * Nombre humano del periodo elegido, para que el reporte hable
     * claramente "del mes/rango elegido" en vez de solo mostrar fechas
     * técnicas. Ej: "Agosto 2026", o "Agosto 2026 – Setiembre 2026" para
     * un rango de 2 meses completos, o un rango exacto de días si no
     * calzan con meses completos.
     */
    private function formatearPeriodo($desde, $hasta) {
        if (!$desde && !$hasta) return 'Todos los registros';
        if (!$desde) return 'Hasta el ' . date('d/m/Y', strtotime($hasta));
        if (!$hasta) return 'Desde el ' . date('d/m/Y', strtotime($desde));

        $meses = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Setiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
        $tsD = strtotime($desde);
        $tsH = strtotime($hasta);

        $esMesCompleto = date('d', $tsD) === '01' && date('Y-m-d', $tsH) === date('Y-m-t', $tsH);
        if ($esMesCompleto) {
            $mD = $meses[(int) date('n', $tsD)] . ' ' . date('Y', $tsD);
            if (date('Y-m', $tsD) === date('Y-m', $tsH)) return $mD;
            $mH = $meses[(int) date('n', $tsH)] . ' ' . date('Y', $tsH);
            return $mD . ' – ' . $mH;
        }

        return date('d/m/Y', $tsD) . ' al ' . date('d/m/Y', $tsH);
    }

    /** KPIs del periodo, calculados desde las reservas ya filtradas. */
    private function calcularKpis($reservas) {
        $ingConf = 0; $ingPend = 0;
        $nConf = 0;   $nCanc  = 0;
        foreach ($reservas as $r) {
            $m = floatval($r->monto);
            if ($r->estado_pago === 'confirmada') { $ingConf += $m; $nConf++; }
            elseif ($r->estado_pago === 'pendiente') { $ingPend += $m; }
            elseif ($r->estado_pago === 'cancelada') { $nCanc++; }
        }
        $nTotal = count($reservas);
        return (object) [
            'ingConf'  => $ingConf,
            'ingPend'  => $ingPend,
            'nConf'    => $nConf,
            'nCanc'    => $nCanc,
            'nTotal'   => $nTotal,
            'ticket'   => $nConf > 0 ? round($ingConf / $nConf, 2) : 0,
            'tasaConv' => $nTotal > 0 ? round($nConf / $nTotal * 100, 1) : 0,
        ];
    }

    // ════════════════════════════════════════════════════════════════
    // EXCEL — HTML Table (abre en Excel sin errores, con estilos)
    // Rediseñado: compacto, un resumen + una tabla, todo acotado al
    // periodo elegido (antes mezclaba datos globales de todos los
    // tiempos con una hoja contable/códigos que no tenían que ver con
    // el rango de fechas seleccionado).
    // ════════════════════════════════════════════════════════════════
    public function excel() {
        $estado      = isset($_GET['estado'])      ? trim($_GET['estado'])      : 'all';
        $fecha_desde = isset($_GET['fecha_desde']) ? trim($_GET['fecha_desde']) : '';
        $fecha_hasta = isset($_GET['fecha_hasta']) ? trim($_GET['fecha_hasta']) : '';

        $reservas   = $this->reporteModel->getReservasParaReporte($estado, $fecha_desde, $fecha_hasta);
        $porPaquete = $this->reporteModel->getResumenPorPaquete($estado, $fecha_desde, $fecha_hasta);
        $k          = $this->calcularKpis($reservas);
        $periodo    = $this->formatearPeriodo($fecha_desde, $fecha_hasta);

        $xe  = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        $mon = fn($v) => 'S/ ' . number_format(floatval($v), 2);

        // Paleta reducida a lo esencial: morado de marca, gris de cabecera,
        // verde/rojo de estado, y una fila clara/oscura alternada.
        $S = [
            'titulo'  => 'background:#7B2FF7;color:#fff;font-weight:bold;font-size:15pt;text-align:center;padding:12px;',
            'periodo' => 'background:#4A148C;color:#fff;font-weight:bold;font-size:12pt;text-align:center;padding:7px;',
            'sub'     => 'background:#F3E5FF;color:#4A148C;font-size:9pt;text-align:center;padding:6px;',
            'kpi_lbl' => 'background:#2D2D2D;color:#fff;font-weight:bold;font-size:8pt;text-align:center;padding:5px;border:1px solid #444;',
            'kpi_val' => 'background:#F8F5FF;color:#7B2FF7;font-weight:bold;font-size:14pt;text-align:center;padding:8px;border:1px solid #7B2FF7;',
            'cab'     => 'background:#2D2D2D;color:#fff;font-weight:bold;font-size:9pt;text-align:center;padding:5px;border:1px solid #555;',
            'cab_izq' => 'background:#2D2D2D;color:#fff;font-weight:bold;font-size:9pt;text-align:left;padding:5px;border:1px solid #555;',
            'nd'      => 'background:#fff;color:#2D2D2D;font-size:9pt;text-align:left;padding:4px;border:1px solid #ddd;',
            'ndc'     => 'background:#fff;color:#2D2D2D;font-size:9pt;text-align:center;padding:4px;border:1px solid #ddd;',
            'ndr'     => 'background:#fff;color:#2D2D2D;font-size:9pt;text-align:right;padding:4px;border:1px solid #ddd;',
            'na'      => 'background:#F8F9FA;color:#2D2D2D;font-size:9pt;text-align:left;padding:4px;border:1px solid #ddd;',
            'nac'     => 'background:#F8F9FA;color:#2D2D2D;font-size:9pt;text-align:center;padding:4px;border:1px solid #ddd;',
            'nar'     => 'background:#F8F9FA;color:#2D2D2D;font-size:9pt;text-align:right;padding:4px;border:1px solid #ddd;',
            'nv'      => 'background:#D4EDDA;color:#155724;font-weight:bold;font-size:9pt;text-align:center;padding:4px;border:1px solid #C3E6CB;',
            'nr'      => 'background:#F8D7DA;color:#721C24;font-weight:bold;font-size:9pt;text-align:center;padding:4px;border:1px solid #F5C6CB;',
            'tot'     => 'background:#7B2FF7;color:#fff;font-weight:bold;font-size:9pt;text-align:right;padding:5px;border:1px solid #5500bb;',
            'totc'    => 'background:#7B2FF7;color:#fff;font-weight:bold;font-size:9pt;text-align:center;padding:5px;border:1px solid #5500bb;',
            'vacio'   => 'background:#fff;padding:3px;',
        ];
        $td = fn($v, $s = 'nd', $c = 1) => '<td colspan="' . $c . '" style="' . $S[$s] . '">' . $xe($v) . '</td>';
        $tr = fn($cells) => '<tr>' . $cells . '</tr>';
        $vr = fn($cols = 1) => '<tr><td colspan="' . $cols . '" style="' . $S['vacio'] . '">&nbsp;</td></tr>';

        ob_start();
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="reporte_happyjumping_' . date('Ymd_His') . '.xls"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head><meta charset="UTF-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets>';
        foreach (['Resumen', 'Detalle de Reservas'] as $sh) {
            echo '<x:ExcelWorksheet><x:Name>' . $sh . '</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet>';
        }
        echo '</x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body>';

        // ════════════════════════════
        // HOJA 1: RESUMEN
        // ════════════════════════════
        $W = 6;
        echo '<table style="border-collapse:collapse;font-family:Arial,sans-serif;" x:str>';
        echo $tr($td('HAPPY JUMPING — REPORTE DE RESERVAS', 'titulo', $W));
        echo $tr($td('Periodo: ' . $periodo, 'periodo', $W));
        echo $tr($td('Estado: ' . ($estado === 'all' ? 'Todos' : ucfirst($estado)) . '   ·   Generado el ' . date('d/m/Y \a \l\a\s H:i'), 'sub', $W));
        echo $vr($W);

        echo $tr(
            $td('RESERVAS', 'kpi_lbl') . $td('CONFIRMADAS (S/)', 'kpi_lbl', 2) .
            $td('PENDIENTES (S/)', 'kpi_lbl', 2) . $td('CANCELADAS', 'kpi_lbl')
        );
        echo $tr(
            $td($k->nTotal, 'kpi_val') . $td($mon($k->ingConf), 'kpi_val', 2) .
            $td($mon($k->ingPend), 'kpi_val', 2) . $td($k->nCanc, 'kpi_val')
        );
        echo $vr($W);
        echo $tr(
            $td('TICKET PROMEDIO', 'kpi_lbl', 3) . $td('TASA DE CONVERSIÓN', 'kpi_lbl', 3)
        );
        echo $tr(
            $td($mon($k->ticket), 'kpi_val', 3) . $td($k->tasaConv . '%', 'kpi_val', 3)
        );
        echo $vr($W);

        echo $tr($td('RESUMEN POR PAQUETE', 'cab_izq', $W));
        echo $tr($td('Paquete', 'cab_izq', 4) . $td('Reservas', 'cab') . $td('Ingresos (S/)', 'cab'));
        $alt = false; $tR = 0; $tI = 0;
        foreach ($porPaquete as $p) {
            $tR += $p->total_reservas; $tI += $p->total_ingresos;
            echo $tr(
                $td($p->paquete, $alt ? 'na' : 'nd', 4) .
                '<td style="' . $S[$alt ? 'nac' : 'ndc'] . '">' . $p->total_reservas . '</td>' .
                '<td style="' . $S[$alt ? 'nar' : 'ndr'] . '">' . $mon($p->total_ingresos) . '</td>'
            );
            $alt = !$alt;
        }
        if (empty($porPaquete)) {
            echo $tr($td('Sin reservas en este periodo.', 'nd', $W));
        } else {
            echo $tr($td('TOTAL', 'totc', 4) . '<td style="' . $S['totc'] . '">' . $tR . '</td><td style="' . $S['tot'] . '">' . $mon($tI) . '</td>');
        }
        echo '</table>';

        // ════════════════════════════
        // HOJA 2: DETALLE DE RESERVAS
        // ════════════════════════════
        echo '<table style="border-collapse:collapse;font-family:Arial,sans-serif;page-break-before:always">';
        echo $tr($td('DETALLE DE RESERVAS', 'titulo', 9));
        echo $tr($td('Periodo: ' . $periodo, 'periodo', 9));
        echo $vr(9);
        echo $tr(
            $td('ID', 'cab') . $td('Fecha', 'cab') . $td('Hora', 'cab') .
            $td('Cumpleañero', 'cab') . $td('Cliente', 'cab') . $td('Paquete', 'cab') .
            $td('Personas', 'cab') . $td('Monto (S/)', 'cab') . $td('Estado', 'cab')
        );

        $alt = false; $sumR = 0;
        foreach ($reservas as $r) {
            $sumR += floatval($r->monto);
            $es = $r->estado_pago;
            $sn = ($es === 'confirmada') ? 'nv' : (($es === 'cancelada') ? 'nr' : ($alt ? 'na' : 'nd'));
            $sc = ($es === 'confirmada') ? 'nv' : (($es === 'cancelada') ? 'nr' : ($alt ? 'nac' : 'ndc'));
            $sm = ($es === 'confirmada') ? 'nv' : (($es === 'cancelada') ? 'nr' : ($alt ? 'nar' : 'ndr'));
            echo $tr(
                '<td style="' . $S[$sc] . '">' . $xe($r->id_reserva) . '</td>' .
                '<td style="' . $S[$sc] . '">' . date('d/m/Y', strtotime($r->fecha)) . '</td>' .
                '<td style="' . $S[$sc] . '">' . substr($r->hora_inicio, 0, 5) . '</td>' .
                '<td style="' . $S[$sn] . '">' . $xe($r->nombre_cumpleanero) . '</td>' .
                '<td style="' . $S[$sn] . '">' . $xe($r->cliente) . '</td>' .
                '<td style="' . $S[$sn] . '">' . $xe($r->paquete) . '</td>' .
                '<td style="' . $S[$sc] . '">' . $xe($r->cantidad_personas) . '</td>' .
                '<td style="' . $S[$sm] . '">' . $mon($r->monto) . '</td>' .
                '<td style="' . $S[$sc] . '">' . ucfirst($es) . '</td>'
            );
            $alt = !$alt;
        }
        if (empty($reservas)) {
            echo $tr($td('No hay reservas para este periodo/filtro.', 'nd', 9));
        } else {
            echo $tr('<td colspan="7" style="' . $S['totc'] . '">TOTAL (' . count($reservas) . ' reservas)</td><td style="' . $S['tot'] . '">' . $mon($sumR) . '</td><td style="' . $S['totc'] . '"></td>');
        }
        echo '</table>';

        echo '</body></html>';
        ob_end_flush();
        exit();
    }

    public function verificar() {
        header('Content-Type: application/json');
        $estado      = isset($_GET['estado'])      ? trim($_GET['estado'])      : 'all';
        $fecha_desde = isset($_GET['fecha_desde']) ? trim($_GET['fecha_desde']) : '';
        $fecha_hasta = isset($_GET['fecha_hasta']) ? trim($_GET['fecha_hasta']) : '';
        $reservas    = $this->reporteModel->getReservasParaReporte($estado, $fecha_desde, $fecha_hasta);
        echo json_encode(['total' => count($reservas)]);
        exit();
    }

    public function pdf() {
        $estado      = isset($_GET['estado'])      ? trim($_GET['estado'])      : 'all';
        $fecha_desde = isset($_GET['fecha_desde']) ? trim($_GET['fecha_desde']) : '';
        $fecha_hasta = isset($_GET['fecha_hasta']) ? trim($_GET['fecha_hasta']) : '';

        $reservas   = $this->reporteModel->getReservasParaReporte($estado, $fecha_desde, $fecha_hasta);
        $porPaquete = $this->reporteModel->getResumenPorPaquete($estado, $fecha_desde, $fecha_hasta);
        $k          = $this->calcularKpis($reservas);
        $periodo    = $this->formatearPeriodo($fecha_desde, $fecha_hasta);

        require_once APP_ROOT . '/../vendor/fpdf/fpdf.php';
        require_once APP_ROOT . '/../vendor/fpdf/ReportePDF.php';

        $pdf = new ReportePDF('L', 'mm', 'A4');
        $pdf->filtroLabel = 'Periodo: ' . $periodo . '   ·   Estado: ' . ($estado === 'all' ? 'Todos' : ucfirst($estado));
        $pdf->AliasNbPages();
        $pdf->SetMargins(10, 30, 10);
        $pdf->SetAutoPageBreak(true, 18);

        // ── Página 1: resumen del periodo ──────────────────────────────
        $pdf->AddPage();

        $this->titulo($pdf, 'RESUMEN DE RESERVAS');
        $pdf->SetFont('Arial', 'B', 12); $pdf->SetFillColor(74, 20, 140); $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(self::PW, 8, 'Periodo: ' . $periodo, 1, 1, 'C', true);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Ln(3);

        $cw = [69, 70, 69, 69];
        $pdf->SetFont('Arial', 'B', 9); $pdf->SetFillColor(45, 45, 45); $pdf->SetTextColor(255, 255, 255);
        $this->fila($pdf, $cw, ['Reservas', 'Confirmadas (S/)', 'Pendientes (S/)', 'Canceladas'], 6, 'B');
        $pdf->SetFont('Arial', 'B', 14); $pdf->SetFillColor(248, 245, 255); $pdf->SetTextColor(123, 47, 247);
        $this->fila($pdf, $cw, [$k->nTotal, 'S/ ' . number_format($k->ingConf, 2), 'S/ ' . number_format($k->ingPend, 2), $k->nCanc], 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Ln(5);

        $cw2 = [138, 139];
        $pdf->SetFont('Arial', 'B', 9); $pdf->SetFillColor(45, 45, 45); $pdf->SetTextColor(255, 255, 255);
        $this->fila($pdf, $cw2, ['Ticket promedio', 'Tasa de conversión'], 6, 'B');
        $pdf->SetFont('Arial', 'B', 14); $pdf->SetFillColor(248, 245, 255); $pdf->SetTextColor(123, 47, 247);
        $this->fila($pdf, $cw2, ['S/ ' . number_format($k->ticket, 2), $k->tasaConv . '%'], 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Ln(8);

        $cp = [141, 68, 68]; $hp = ['Paquete', 'Reservas', 'Ingresos (S/)'];
        $this->titulo($pdf, 'RESUMEN POR PAQUETE');
        $this->cabecera($pdf, $cp, $hp, 7);
        if (empty($porPaquete)) {
            $pdf->SetFont('Arial', 'I', 9); $pdf->SetFillColor(255, 255, 255); $pdf->SetTextColor(120, 120, 120);
            $pdf->Cell(array_sum($cp), 8, 'Sin reservas en este periodo.', 1, 1, 'C', true);
            $pdf->SetTextColor(0, 0, 0);
        } else {
            foreach ($porPaquete as $p) {
                $pdf->SetFont('Arial', '', 9); $pdf->SetTextColor(0, 0, 0); $pdf->SetFillColor(248, 245, 255);
                $pdf->Cell($cp[0], 6, $p->paquete, 1, 0, 'L', true);
                $pdf->Cell($cp[1], 6, $p->total_reservas, 1, 0, 'C', true);
                $pdf->Cell($cp[2], 6, 'S/ ' . number_format($p->total_ingresos, 2), 1, 1, 'R', true);
            }
        }

        // ── Página(s) siguientes: detalle de reservas del periodo ──────
        $pdf->AddPage(); $pdf->SetTextColor(0, 0, 0);
        $cr = [12, 24, 16, 48, 48, 42, 20, 30, 37];
        $hr = ['ID', 'Fecha', 'Hora', 'Cumpleañero', 'Cliente', 'Paquete', 'Personas', 'Monto (S/)', 'Estado'];
        $ar = ['C', 'C', 'C', 'L', 'L', 'L', 'C', 'R', 'C'];
        $this->titulo($pdf, 'DETALLE DE RESERVAS (' . count($reservas) . ' registros)');
        $this->cabecera($pdf, $cr, $hr);
        $fill = false;
        foreach ($reservas as $r) {
            $pdf->SetFont('Arial', '', 8); $pdf->SetTextColor(0, 0, 0);
            if ($r->estado_pago === 'confirmada') { $pdf->SetFillColor(220, 255, 220); }
            elseif ($r->estado_pago === 'cancelada') { $pdf->SetFillColor(255, 220, 220); }
            else { $pdf->SetFillColor($fill ? 248 : 255, $fill ? 248 : 255, 255); }
            $vals = [
                $r->id_reserva, date('d/m/Y', strtotime($r->fecha)), substr($r->hora_inicio, 0, 5),
                $this->t($r->nombre_cumpleanero, 26), $this->t($r->cliente, 26), $this->t($r->paquete, 24),
                $r->cantidad_personas, number_format($r->monto, 2), ucfirst($r->estado_pago),
            ];
            foreach ($vals as $i => $v) $pdf->Cell($cr[$i], 6, $v, 1, ($i === 8) ? 1 : 0, $ar[$i], true);
            $fill = !$fill;
        }
        if (empty($reservas)) {
            $pdf->SetFont('Arial', 'I', 9); $pdf->SetFillColor(255, 255, 255); $pdf->SetTextColor(120, 120, 120);
            $pdf->Cell(array_sum($cr), 8, 'No hay reservas para este periodo/filtro.', 1, 1, 'C', true);
        } else {
            $tot = array_sum(array_map(fn($r) => $r->monto, $reservas));
            $izq = array_sum($cr) - $cr[7] - $cr[8];
            $pdf->SetFont('Arial', 'B', 8); $pdf->SetFillColor(123, 47, 247); $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell($izq, 6, 'TOTAL', 1, 0, 'R', true);
            $pdf->Cell($cr[7], 6, 'S/' . number_format($tot, 2), 1, 0, 'R', true);
            $pdf->Cell($cr[8], 6, '', 1, 1, 'C', true);
        }

        $pdf->Output('D', 'reporte_happyjumping_' . date('Ymd_His') . '.pdf');
        exit();
    }

    // ── Helpers PDF ──────────────────────────────────────────────────────────
    private function titulo($pdf,$texto) {
        $pdf->SetFont('Arial','B',10); $pdf->SetFillColor(127,0,255); $pdf->SetTextColor(255,255,255);
        $pdf->Cell(self::PW,7,$texto,1,1,'C',true); $pdf->SetTextColor(0,0,0);
    }
    private function cabecera($pdf,$widths,$headers,$h=6) {
        $pdf->SetFont('Arial','B',8); $pdf->SetFillColor(50,50,50); $pdf->SetTextColor(255,255,255);
        $last=count($headers)-1;
        foreach ($headers as $i=>$hdr) $pdf->Cell($widths[$i],$h,$hdr,1,($i===$last)?1:0,'C',true);
        $pdf->SetTextColor(0,0,0);
    }
    private function fila($pdf,$widths,$vals,$h=6,$style='') {
        if ($style==='B') $pdf->SetFont('Arial','B',9);
        $last=count($vals)-1;
        foreach ($vals as $i=>$v) $pdf->Cell($widths[$i],$h,$v,1,($i===$last)?1:0,'C',true);
    }
    private function t($texto,$max) {
        $texto=(string)$texto;
        return mb_strlen($texto)>$max ? mb_substr($texto,0,$max-2).'..' : $texto;
    }
}