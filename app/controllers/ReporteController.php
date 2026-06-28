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

    // ════════════════════════════════════════════════════════════════
    // EXCEL — HTML Table (abre en Excel sin errores, con estilos)
    // ════════════════════════════════════════════════════════════════
    public function excel() {
        $estado      = isset($_GET['estado'])      ? trim($_GET['estado'])      : 'all';
        $fecha_desde = isset($_GET['fecha_desde']) ? trim($_GET['fecha_desde']) : '';
        $fecha_hasta = isset($_GET['fecha_hasta']) ? trim($_GET['fecha_hasta']) : '';

        $reservas   = $this->reporteModel->getReservasParaReporte($estado, $fecha_desde, $fecha_hasta);
        $totales    = $this->reporteModel->getTotalesGenerales();
        $porPaquete = $this->reporteModel->getResumenPorPaquete();
        $codigos    = $this->reporteModel->getCodigosParaReporte('all');
        $totCod     = $this->reporteModel->getTotalesCodigos();

        // Cálculos contables — todos desde las $reservas FILTRADAS por periodo
        $ingConf = 0; $ingPend = 0; $ingCanc = 0;
        $nConf = 0;   $nPend  = 0;  $nCanc  = 0;
        foreach ($reservas as $r) {
            $m = floatval($r->monto);
            if ($r->estado_pago === 'confirmada') { $ingConf += $m; $nConf++; }
            if ($r->estado_pago === 'pendiente')  { $ingPend += $m; $nPend++; }
            if ($r->estado_pago === 'cancelada')  { $ingCanc += $m; $nCanc++; }
        }
        $nTotal   = count($reservas);
        $sumMonto = $ingConf + $ingPend + $ingCanc;
        $ticket   = $nConf > 0 ? round($ingConf / $nConf, 2) : 0;
        $tasaConv = $nTotal > 0 ? round($nConf  / $nTotal * 100, 1) : 0;
        $tasaCanc = $nTotal > 0 ? round($nCanc  / $nTotal * 100, 1) : 0;

        $xe  = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
        $mon = fn($v) => 'S/ ' . number_format(floatval($v), 2);

        $S = [
            'titulo'   => 'background:#7F00FF;color:#fff;font-weight:bold;font-size:14pt;text-align:center;padding:10px;',
            'seccion'  => 'background:#EDE7F6;color:#4A148C;font-weight:bold;font-size:10pt;padding:6px;',
            'seccion_r'=> 'background:#FFEBEE;color:#B71C1C;font-weight:bold;font-size:10pt;padding:6px;',
            'cab'      => 'background:#2D2D2D;color:#fff;font-weight:bold;font-size:9pt;text-align:center;padding:5px;border:1px solid #555;',
            'cab_izq'  => 'background:#2D2D2D;color:#fff;font-weight:bold;font-size:9pt;text-align:left;padding:5px;border:1px solid #555;',
            'kpi_lbl'  => 'background:#F3E5FF;color:#4A148C;font-weight:bold;font-size:9pt;text-align:center;padding:5px;border:1px solid #CE93D8;',
            'kpi_val'  => 'background:#fff;color:#7F00FF;font-weight:bold;font-size:16pt;text-align:center;padding:8px;border:2px solid #7F00FF;',
            'kpi_mon'  => 'background:#7F00FF;color:#fff;font-weight:bold;font-size:16pt;text-align:center;padding:8px;',
            'nd'       => 'background:#fff;color:#2D2D2D;font-size:9pt;text-align:left;padding:4px;border:1px solid #ddd;',
            'ndc'      => 'background:#fff;color:#2D2D2D;font-size:9pt;text-align:center;padding:4px;border:1px solid #ddd;',
            'ndr'      => 'background:#fff;color:#2D2D2D;font-size:9pt;text-align:right;padding:4px;border:1px solid #ddd;',
            'na'       => 'background:#F8F9FA;color:#2D2D2D;font-size:9pt;text-align:left;padding:4px;border:1px solid #ddd;',
            'nac'      => 'background:#F8F9FA;color:#2D2D2D;font-size:9pt;text-align:center;padding:4px;border:1px solid #ddd;',
            'nar'      => 'background:#F8F9FA;color:#2D2D2D;font-size:9pt;text-align:right;padding:4px;border:1px solid #ddd;',
            'nv'       => 'background:#D4EDDA;color:#155724;font-weight:bold;font-size:9pt;text-align:center;padding:4px;border:1px solid #C3E6CB;',
            'nr'       => 'background:#F8D7DA;color:#721C24;font-weight:bold;font-size:9pt;text-align:center;padding:4px;border:1px solid #F5C6CB;',
            'ny'       => 'background:#FFF9C4;color:#333;font-size:9pt;text-align:right;padding:4px;border:2px solid #F9A825;',
            'nf'       => 'background:#E8F5E9;color:#1B5E20;font-weight:bold;font-size:9pt;text-align:right;padding:4px;border:1px solid #A5D6A7;',
            'tot'      => 'background:#7F00FF;color:#fff;font-weight:bold;font-size:9pt;text-align:right;padding:5px;border:1px solid #5500bb;',
            'totc'     => 'background:#7F00FF;color:#fff;font-weight:bold;font-size:9pt;text-align:center;padding:5px;border:1px solid #5500bb;',
            'sub'      => 'background:#4527A0;color:#fff;font-weight:bold;font-size:10pt;text-align:right;padding:5px;',
            'subc'     => 'background:#4527A0;color:#fff;font-weight:bold;font-size:10pt;text-align:center;padding:5px;',
            'subr'     => 'background:#B71C1C;color:#fff;font-weight:bold;font-size:10pt;text-align:right;padding:5px;',
            'res'      => 'background:#00695C;color:#fff;font-weight:bold;font-size:12pt;text-align:right;padding:8px;',
            'resc'     => 'background:#00695C;color:#fff;font-weight:bold;font-size:11pt;text-align:left;padding:8px;',
            'nota'     => 'background:#ECEFF1;color:#455A64;font-style:italic;font-size:8pt;text-align:left;padding:4px;',
            'meta'     => 'background:#EEEEEE;color:#2D2D2D;font-size:9pt;text-align:left;padding:4px;',
            'metab'    => 'background:#EEEEEE;color:#2D2D2D;font-weight:bold;font-size:9pt;text-align:left;padding:4px;',
            'vacio'    => 'background:#fff;padding:3px;',
        ];
        $td = fn($v,$s='nd',$c=1) => '<td colspan="'.$c.'" style="'.$S[$s].'">'.$xe($v).'</td>';
        $tn = fn($v,$s='ndr')     => '<td style="'.$S[$s].'">'.($v!==null?$mon($v):'').'</td>';
        $tr = fn($cells)          => '<tr>'.$cells.'</tr>';
        $vr = fn($cols=1)         => '<tr><td colspan="'.$cols.'" style="'.$S['vacio'].'">&nbsp;</td></tr>';

        ob_start();

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="reporte_happyjumping_' . date('Ymd_His') . '.xls"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head><meta charset="UTF-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets>';
        foreach (['Reservas','Por Paquete','Codigos','Contabilidad'] as $sh) {
            echo '<x:ExcelWorksheet><x:Name>'.$sh.'</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet>';
        }
        echo '</x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body>';

        // ════════════════════════════
        // HOJA 1: RESERVAS
        // ════════════════════════════
        echo '<table style="border-collapse:collapse;font-family:Arial,sans-serif;" x:str>';
        echo $tr($td('REPORTE DE RESERVAS — HAPPY JUMPING','titulo',13));
        echo $tr($td('Generado: '.date('d/m/Y H:i:s').'   |   Estado: '.($estado==='all'?'Todos':strtoupper($estado)).'   |   Desde: '.($fecha_desde?:'Sin filtro').'   |   Hasta: '.($fecha_hasta?:'Sin filtro'),'meta',13));
        echo $vr(13);

        $periodoLabel = ($fecha_desde && $fecha_hasta)
            ? 'Periodo: '.date('d/m/Y',strtotime($fecha_desde)).' al '.date('d/m/Y',strtotime($fecha_hasta))
            : 'Todos los registros';
        echo $tr($td('— '.$periodoLabel.' —','nota',13));
        echo $tr(
            $td('Reservas en el periodo','kpi_lbl',2).'<td style="'.$S['vacio'].'"></td>'.
            $td('Ingresos confirmados del periodo','kpi_lbl',3).'<td style="'.$S['vacio'].'"></td>'.
            $td('Monto pendiente','kpi_lbl',2).'<td style="'.$S['vacio'].'"></td>'.
            $td('Canceladas','kpi_lbl',2)
        );
        echo $tr(
            '<td colspan="2" style="'.$S['kpi_val'].'">'.$nTotal.'</td><td style="'.$S['vacio'].'"></td>'.
            '<td colspan="3" style="'.$S['kpi_mon'].'">'.$mon($ingConf).'</td><td style="'.$S['vacio'].'"></td>'.
            '<td colspan="2" style="'.$S['kpi_val'].'">'.$mon($ingPend).'</td><td style="'.$S['vacio'].'"></td>'.
            '<td colspan="2" style="'.$S['kpi_val'].'">'.$nCanc.'</td>'
        );
        echo $vr(13);

        $ingConfTot = floatval($totales->ingresos_confirmados ?? 0);
        $S['ref_lbl'] = 'background:#F3E5FF;color:#4A148C;font-size:8pt;font-style:italic;text-align:center;padding:3px;border:1px solid #CE93D8;';
        $S['ref_val'] = 'background:#F3E5FF;color:#4A148C;font-weight:bold;font-size:9pt;text-align:right;padding:3px;border:1px solid #CE93D8;';
        echo $tr(
            '<td colspan="5" style="'.$S['ref_lbl'].'">Total historico confirmado (todos los periodos, solo referencia):</td>'.
            '<td colspan="3" style="'.$S['ref_val'].'">'.$mon($ingConfTot).'</td>'.
            '<td colspan="5" style="'.$S['ref_lbl'].'">Este dato NO corresponde al periodo filtrado arriba</td>'
        );
        echo $vr(13);

        echo $tr(
            $td('ID','cab').$td('Fecha','cab').$td('H.Inicio','cab').$td('H.Fin','cab').
            $td('Cumpleanero','cab').$td('Cliente','cab').$td('Paquete','cab').
            $td('Personas','cab').$td('Edad','cab').$td('Correo','cab').
            $td('Monto','cab').$td('Estado','cab').$td('Observaciones','cab')
        );

        $alt = false; $sumR = 0;
        foreach ($reservas as $r) {
            $sumR += floatval($r->monto);
            $es = $r->estado_pago;
            $sn = ($es==='confirmada')?'nv':(($es==='cancelada')?'nr':($alt?'na':'nd'));
            $sc = ($es==='confirmada')?'nv':(($es==='cancelada')?'nr':($alt?'nac':'ndc'));
            $sm = ($es==='confirmada')?'nv':(($es==='cancelada')?'nr':($alt?'nar':'ndr'));
            echo $tr(
                '<td style="'.$S[$sc].'">'.$xe($r->id_reserva).'</td>'.
                '<td style="'.$S[$sc].'">'.date('d/m/Y',strtotime($r->fecha)).'</td>'.
                '<td style="'.$S[$sc].'">'.substr($r->hora_inicio,0,5).'</td>'.
                '<td style="'.$S[$sc].'">'.substr($r->hora_fin,0,5).'</td>'.
                '<td style="'.$S[$sn].'">'.$xe($r->nombre_cumpleanero).'</td>'.
                '<td style="'.$S[$sn].'">'.$xe($r->cliente).'</td>'.
                '<td style="'.$S[$sn].'">'.$xe($r->paquete).'</td>'.
                '<td style="'.$S[$sc].'">'.$xe($r->cantidad_personas).'</td>'.
                '<td style="'.$S[$sc].'">'.$xe($r->edad_cumpleanero).'</td>'.
                '<td style="'.$S[$sn].'">'.$xe($r->correo_cliente).'</td>'.
                '<td style="'.$S[$sm].'">'.$mon($r->monto).'</td>'.
                '<td style="'.$S[$sc].'">'.strtoupper($es).'</td>'.
                '<td style="'.$S[$sn].'">'.$xe($r->observaciones??'').'</td>'
            );
            $alt=!$alt;
        }
        echo $tr('<td colspan="10" style="'.$S['totc'].'">TOTAL ('.count($reservas).' reservas)</td><td style="'.$S['tot'].'">'.$mon($sumR).'</td><td colspan="2" style="'.$S['totc'].'"></td>');
        echo '</table>';
        echo '<br style="mso-data-placement:same-cell"/>';

        // ════════════════════════════
        // HOJA 2: PAQUETES
        // ════════════════════════════
        echo '<table style="border-collapse:collapse;font-family:Arial,sans-serif;page-break-before:always">';
        echo $tr($td('RESUMEN POR PAQUETE','titulo',3));
        echo $vr(3);
        echo $tr($td('Paquete','cab_izq').$td('Total Reservas','cab').$td('Ingresos (S/)','cab'));
        $alt=false; $tR=0; $tI=0;
        foreach ($porPaquete as $p) {
            $tR+=$p->total_reservas; $tI+=$p->total_ingresos;
            echo $tr($td($p->paquete,$alt?'na':'nd').'<td style="'.$S[$alt?'nac':'ndc'].'">'.$p->total_reservas.'</td>'.'<td style="'.$S[$alt?'nar':'ndr'].'">'.$mon($p->total_ingresos).'</td>');
            $alt=!$alt;
        }
        echo $tr('<td style="'.$S['totc'].'">TOTAL</td><td style="'.$S['totc'].'">'.$tR.'</td><td style="'.$S['tot'].'">'.$mon($tI).'</td>');
        echo '</table>';

        // ════════════════════════════
        // HOJA 3: CODIGOS
        // ════════════════════════════
        echo '<table style="border-collapse:collapse;font-family:Arial,sans-serif;page-break-before:always">';
        echo $tr($td('CODIGOS DE PROMOCION','titulo',9));
        echo $vr(9);
        echo $tr(
            $td('Total','kpi_lbl',3).'<td style="'.$S['vacio'].'"></td>'.
            $td('Disponibles','kpi_lbl',2).'<td style="'.$S['vacio'].'"></td>'.
            $td('Usados','kpi_lbl',2)
        );
        echo $tr(
            '<td colspan="3" style="'.$S['kpi_val'].'">'.$totCod->total.'</td><td style="'.$S['vacio'].'"></td>'.
            '<td colspan="2" style="'.$S['kpi_val'].'">'.$totCod->disponibles.'</td><td style="'.$S['vacio'].'"></td>'.
            '<td colspan="2" style="'.$S['kpi_val'].'">'.$totCod->usados.'</td>'
        );
        echo $vr(9);
        echo $tr($td('ID','cab').$td('Codigo','cab').$td('Promocion','cab').$td('Puntos','cab').$td('Usuario','cab').$td('Correo','cab').$td('Estado','cab').$td('Fec. Generacion','cab').$td('Fec. Uso','cab'));
        foreach ($codigos as $cod) {
            $sc = $cod->estado==='disponible'?'nv':'nr';
            echo $tr(
                '<td style="'.$S[$sc].'">'.$xe($cod->id_codigo).'</td>'.
                '<td style="'.$S[$sc].'">'.$xe($cod->codigo).'</td>'.
                '<td style="'.$S[$sc].'">'.$xe($cod->nombre_promocion).'</td>'.
                '<td style="'.$S[$sc].'">'.$xe($cod->puntos_necesarios).'</td>'.
                '<td style="'.$S[$sc].'">'.$xe($cod->nombre_usuario).'</td>'.
                '<td style="'.$S[$sc].'">'.$xe($cod->correo_usuario).'</td>'.
                '<td style="'.$S[$sc].'">'.(($cod->estado==='disponible')?'DISPONIBLE':'USADO').'</td>'.
                '<td style="'.$S[$sc].'">'.date('d/m/Y H:i',strtotime($cod->fecha_generacion)).'</td>'.
                '<td style="'.$S[$sc].'">'.($cod->fecha_uso?date('d/m/Y H:i',strtotime($cod->fecha_uso)):'-').'</td>'
            );
        }
        echo '</table>';

        // ════════════════════════════
        // HOJA 4: CONTABILIDAD
        // ════════════════════════════
        echo '<table style="border-collapse:collapse;font-family:Arial,sans-serif;page-break-before:always">';
        $W = 6;
        echo $tr($td('HOJA DE TRABAJO CONTABLE - HAPPY JUMPING  |  '.date('d/m/Y'),'titulo',$W));
        echo $tr($td('Celdas AMARILLAS = ingrese valores manualmente. Celdas VERDES = calculadas automaticamente.','nota',$W));
        echo $vr($W);

        echo $tr($td('SECCION 1 - ESTADO DE RESULTADOS DEL PERIODO','seccion',$W));
        echo $vr($W);
        echo $tr($td('INGRESOS','cab',2).$td('IMPORTE','cab').'<td style="'.$S['vacio'].'"></td>'.$td('EGRESOS / COSTOS','cab',2).$td('IMPORTE','cab'));

        $ingItems = [
            ['Ingresos confirmados (sistema)', $ingConf, false],
            ['Ingresos pendientes de cobro',   $ingPend, false],
            ['Descuentos / devoluciones',       null,     true],
            ['Otros ingresos',                  null,     true],
        ];
        $egrItems = ['Alquiler / arrendamiento','Sueldos y planilla','Servicios basicos','Mantenimiento de equipos','Materiales y suministros','Publicidad y marketing','Honorarios / comisiones','Seguros','Depreciacion de activos','Otros gastos'];

        $maxR = max(count($ingItems), count($egrItems));
        for ($i=0; $i<$maxR; $i++) {
            $cI = isset($ingItems[$i])
                ? '<td colspan="2" style="'.$S['nd'].'">'.$xe($ingItems[$i][0]).'</td>'.($ingItems[$i][2]?'<td style="'.$S['ny'].'">&nbsp;</td>':'<td style="'.$S['ndr'].'">'.$mon($ingItems[$i][1]).'</td>')
                : '<td colspan="3" style="'.$S['vacio'].'"></td>';
            $cE = isset($egrItems[$i])
                ? '<td colspan="2" style="'.$S['nd'].'">'.$xe($egrItems[$i]).'</td><td style="'.$S['ny'].'">&nbsp;</td>'
                : '<td colspan="3" style="'.$S['vacio'].'"></td>';
            echo $tr($cI.'<td style="'.$S['vacio'].'"></td>'.$cE);
        }
        echo $vr($W);
        $sumIngSistema = $ingConf + $ingPend;
        echo $tr('<td colspan="2" style="'.$S['subc'].'">TOTAL INGRESOS</td><td style="'.$S['sub'].'">'.$mon($sumIngSistema).' + editables</td><td style="'.$S['vacio'].'"></td><td colspan="2" style="'.$S['subc'].'">TOTAL EGRESOS</td><td style="'.$S['subr'].'">(suma celdas amarillas)</td>');
        echo $vr($W);
        echo $tr('<td colspan="2" style="'.$S['resc'].'">UTILIDAD / PERDIDA DEL PERIODO</td><td style="'.$S['res'].'">Total Ingresos - Total Egresos</td><td colspan="3" style="'.$S['nota'].'">Complete los egresos en amarillo para calcular</td>');
        echo $vr($W);

        echo $tr($td('SECCION 2 - CALCULO DE IGV Y TRIBUTOS','seccion',$W));
        echo $vr($W);
        echo $tr($td('CONCEPTO','cab',2).$td('IMPORTE','cab').'<td style="'.$S['vacio'].'"></td>'.$td('REFERENCIA / FORMULA','cab',2).'<td></td>');
        $tributos = [
            ['Base imponible (ventas afectas)',         $ingConf,       false, 'Ingresos confirmados del sistema'],
            ['IGV debito fiscal (18%)',                 $ingConf*0.18,  false, 'Base x 18% = '.$mon($ingConf*0.18)],
            ['IGV credito fiscal (compras)',            null,           true,  'Completar con facturas de compra'],
            ['IGV neto a pagar (debito - credito)',     null,           true,  'Debito - Credito fiscal'],
            ['Imp. a la renta (1.5% ventas mensuales)', $ingConf*0.015, false, 'Base x 1.5% = '.$mon($ingConf*0.015)],
            ['Essalud empleados (9% planilla)',         null,           true,  'Completar con monto planilla'],
            ['AFP / ONP trabajadores',                  null,           true,  'Completar con boletas'],
        ];
        foreach ($tributos as $t) {
            echo $tr('<td colspan="2" style="'.$S['nd'].'">'.$xe($t[0]).'</td>'.($t[2]?'<td style="'.$S['ny'].'">&nbsp;</td>':'<td style="'.$S['ndr'].'">'.$mon($t[1]).'</td>').'<td style="'.$S['vacio'].'"></td><td colspan="2" style="'.$S['nota'].'">'.$xe($t[3]).'</td><td></td>');
        }
        echo $vr($W);

        echo $tr($td('SECCION 3 - INDICADORES FINANCIEROS','seccion',$W));
        echo $vr($W);
        echo $tr($td('INDICADOR','cab',2).$td('VALOR','cab').'<td style="'.$S['vacio'].'"></td>'.$td('DESCRIPCION','cab',2).'<td></td>');
        $indicadores = [
            ['Ticket promedio por reserva',     $mon($ticket),       'Ingresos confirmados / Reservas confirmadas'],
            ['Tasa de conversion',              $tasaConv.'%',       'Confirmadas / Total x 100'],
            ['Tasa de cancelacion',             $tasaCanc.'%',       'Canceladas / Total x 100'],
            ['Ingresos pendientes de cobro',    $mon($ingPend),      'Suma reservas en estado pendiente'],
            ['Total reservas en el periodo',    $nTotal.' reservas', 'Incluye todos los estados'],
            ['Reservas confirmadas',            $nConf.' reservas',  'Solo estado confirmada'],
        ];
        foreach ($indicadores as $ind) {
            echo $tr('<td colspan="2" style="'.$S['nd'].'">'.$xe($ind[0]).'</td><td style="'.$S['nf'].'">'.$xe($ind[1]).'</td><td style="'.$S['vacio'].'"></td><td colspan="2" style="'.$S['nota'].'">'.$xe($ind[2]).'</td><td></td>');
        }
        echo $vr($W);

        echo $tr($td('SECCION 4 - LIBRO DE ASIENTOS CONTABLES','seccion',$W));
        echo $tr($td('Complete la fecha, cuenta contable, montos al Debe y Haber de cada asiento del periodo.','nota',$W));
        echo $vr($W);
        echo $tr($td('N','cab').$td('Fecha','cab').$td('Cuenta Contable / Descripcion','cab').$td('Debe (S/)','cab').$td('Haber (S/)','cab').$td('Referencia','cab'));
        for ($n=1; $n<=20; $n++) {
            echo $tr('<td style="'.$S['ndc'].'">'.$n.'</td><td style="'.$S['ny'].'">&nbsp;</td><td style="'.$S['ny'].'">&nbsp;</td><td style="'.$S['ny'].'">&nbsp;</td><td style="'.$S['ny'].'">&nbsp;</td><td style="'.$S['ny'].'">&nbsp;</td>');
        }
        echo $tr('<td colspan="3" style="'.$S['totc'].'">TOTALES</td><td style="'.$S['tot'].'">(suma Debe)</td><td style="'.$S['tot'].'">(suma Haber)</td><td style="'.$S['totc'].'">Debe = Haber si balancea</td>');
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
        $totales    = $this->reporteModel->getTotalesGenerales();
        $porPaquete = $this->reporteModel->getResumenPorPaquete();
        $codigos    = $this->reporteModel->getCodigosParaReporte('all');
        $totCod     = $this->reporteModel->getTotalesCodigos();

        require_once APP_ROOT . '/../vendor/fpdf/fpdf.php';
        require_once APP_ROOT . '/../vendor/fpdf/ReportePDF.php';

        $pdf = new ReportePDF('L', 'mm', 'A4');
        $pdf->filtroLabel = 'Estado: ' . ($estado==='all'?'Todos':strtoupper($estado))
                          . '  |  Desde: ' . ($fecha_desde ?: 'Sin filtro')
                          . '  |  Hasta: ' . ($fecha_hasta ?: 'Sin filtro');
        $pdf->AliasNbPages();
        $pdf->SetMargins(10, 30, 10);
        $pdf->SetAutoPageBreak(true, 18);

        $pdf->AddPage();
        $cw = [69, 70, 69, 69];
        $this->titulo($pdf,'RESUMEN DE RESERVAS');
        $pdf->SetFont('Arial','B',9); $pdf->SetFillColor(240,230,255); $pdf->SetTextColor(0,0,0);
        $this->fila($pdf,$cw,['Total Reservas','Ingresos Confirmados','Pendientes','Canceladas'],6,'B');
        $pdf->SetFont('Arial','B',13); $pdf->SetFillColor(255,255,255);
        $this->fila($pdf,$cw,[$totales->total_reservas,'S/ '.number_format($totales->ingresos_confirmados,2),$totales->pendientes,$totales->canceladas],9);
        $pdf->Ln(5);

        $cr=[10,21,14,34,35,38,23,26,13,63];
        $hr=['ID','Fecha','Hora','Cumpleanero','Paquete','Cliente','Monto (S/)','Estado','Per.','Observaciones'];
        $ar=['C','C','C','L','L','L','R','C','C','L'];
        $this->titulo($pdf,'DETALLE DE RESERVAS ('.count($reservas).' registros)');
        $this->cabecera($pdf,$cr,$hr);
        $fill=false;
        foreach ($reservas as $r) {
            $pdf->SetFont('Arial','',7); $pdf->SetTextColor(0,0,0);
            if ($r->estado_pago==='confirmada') { $pdf->SetFillColor(220,255,220); }
            elseif ($r->estado_pago==='cancelada') { $pdf->SetFillColor(255,220,220); }
            else { $pdf->SetFillColor($fill?248:255,$fill?248:255,255); }
            $vals=[$r->id_reserva,date('d/m/Y',strtotime($r->fecha)),substr($r->hora_inicio,0,5),
                $this->t($r->nombre_cumpleanero,22),$this->t($r->paquete,22),$this->t($r->cliente,25),
                number_format($r->monto,2),strtoupper($r->estado_pago),$r->cantidad_personas,$this->t($r->observaciones??'',40)];
            foreach ($vals as $i=>$v) $pdf->Cell($cr[$i],6,$v,1,($i===9)?1:0,$ar[$i],true);
            $fill=!$fill;
        }
        $tot=array_sum(array_map(fn($r)=>$r->monto,$reservas));
        $izq=array_sum($cr)-$cr[6]-$cr[7]-$cr[8]-$cr[9];
        $pdf->SetFont('Arial','B',8); $pdf->SetFillColor(127,0,255); $pdf->SetTextColor(255,255,255);
        $pdf->Cell($izq,6,'TOTAL',1,0,'R',true);
        $pdf->Cell($cr[6],6,'S/'.number_format($tot,2),1,0,'R',true);
        $pdf->Cell($cr[7]+$cr[8]+$cr[9],6,'',1,1,'C',true);

        $pdf->AddPage(); $pdf->SetTextColor(0,0,0);
        $cp=[140,69,68]; $hp=['Paquete','Total Reservas','Ingresos (S/)'];
        $this->titulo($pdf,'RESUMEN POR PAQUETE');
        $this->cabecera($pdf,$cp,$hp,7);
        foreach ($porPaquete as $p) {
            $pdf->SetFont('Arial','',9); $pdf->SetTextColor(0,0,0); $pdf->SetFillColor(248,245,255);
            $pdf->Cell($cp[0],6,$p->paquete,1,0,'L',true);
            $pdf->Cell($cp[1],6,$p->total_reservas,1,0,'C',true);
            $pdf->Cell($cp[2],6,'S/ '.number_format($p->total_ingresos,2),1,1,'R',true);
        }
        $pdf->Ln(8);

        $cwc=[92,92,93];
        $this->titulo($pdf,'RESUMEN DE CODIGOS DE PROMOCION');
        $pdf->SetFont('Arial','B',9); $pdf->SetFillColor(240,230,255); $pdf->SetTextColor(0,0,0);
        $this->fila($pdf,$cwc,['Total Codigos','Disponibles','Usados'],6,'B');
        $pdf->SetFont('Arial','B',13); $pdf->SetFillColor(255,255,255);
        $this->fila($pdf,$cwc,[$totCod->total,$totCod->disponibles,$totCod->usados],9);
        $pdf->Ln(8);

        $cc=[10,26,48,15,44,60,26,25,23];
        $hc=['ID','Codigo','Promocion','Pts','Usuario','Correo','Estado','Generacion','Uso'];
        $ac=['C','C','L','C','L','L','C','C','C'];
        $this->titulo($pdf,'DETALLE DE CODIGOS ('.count($codigos).' registros)');
        $this->cabecera($pdf,$cc,$hc);
        foreach ($codigos as $cod) {
            $pdf->SetFont('Arial','',7); $pdf->SetTextColor(0,0,0);
            $pdf->SetFillColor($cod->estado==='disponible'?220:255,$cod->estado==='disponible'?255:220,$cod->estado==='disponible'?220:220);
            $vv=[$cod->id_codigo,$cod->codigo,$this->t($cod->nombre_promocion,30),$cod->puntos_necesarios,
                $this->t($cod->nombre_usuario,28),$this->t($cod->correo_usuario,36),strtoupper($cod->estado),
                date('d/m/Y',strtotime($cod->fecha_generacion)),$cod->fecha_uso?date('d/m/Y',strtotime($cod->fecha_uso)):'-'];
            foreach ($vv as $i=>$v) $pdf->Cell($cc[$i],6,$v,1,($i===8)?1:0,$ac[$i],true);
        }

        $pdf->Output('D','reporte_happyjumping_'.date('Ymd_His').'.pdf');
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