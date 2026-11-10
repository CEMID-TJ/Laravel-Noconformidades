<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NoConformidadController extends Controller
{
    private $mTipoRegistro = [
        'No Conformidad' => 'Incumplimiento de un requisito Legal o de procedimientos internos que no afecta la prestación del servicio educativo o la oferta de valor con los clientes (Estudiantes y padres de familia)',
        'Servicio no Conforme' => 'Incumplimiento de requisito directamente relacionado con el servicio que se entrega al cliente, es decir, el servicio educativo, y que se presenta durante la prestación de este.',
        'Corrección' => 'Acción para eliminar de manera inmediata un fallo o desviación detectada.',
        'Acción Preventiva' => 'Acción desarrollada para evitar que un riesgo o desarrollo de una actividad se materialicé en No conformidad o servicio no conforme.',
        'Acción Correctiva' => 'Acción para eliminar la causa de una no conformidad y evitar que vuelva a ocurrir',
        'Acción de Mejora' => 'Ajuste propuesto con el fin de mejorar el desempeño, agilizar la acción o aumentar positivamente la percepción de las partes interesadas.'
    ];

    // Equivalente a las variables de sesión de tu sistema heredado
    private function getSessionData()
    {
        $usuario = session('Usuario', '1054540722'); // Johan Rodriguez por defecto en pruebas
        $nomUsuario = session('NomUsuario', 'Johan Rodriguez');
        $perfil = session('Perfil', 'ADMIN'); // ADMIN, DIREC, DUEPR

        $esAdminPerfil = $this->inList($perfil, 'ADMIN');

        $adminList = [
            $esAdminPerfil ? $usuario : '98381246',
            '79558178',   // CAMILO VELANDIA
            '1054540722', // Johan Rodriguez
            '1014305622', // Laura Valentina
            '1073696610', // Jenniffer Bello
            '52841712',   // Diana Álvarez
            '1233909122', // Eduardo León
            '1022928448'  // JABIER BARRERA
        ];

        $reversarList = [
            $esAdminPerfil ? $usuario : '98381246',
            '1014305622', // Laura Valentina
            '1073696610', // Jenniffer Bello
            '1233909122', // Eduardo León
            '1022928448'  // JABIER BARRERA
        ];

        $generalList = [
            $esAdminPerfil ? $usuario : '98381246',
            '39665007', '79404413', '8730314', '37829191', '40025213', '41405252',
            '79879905', '39668167', '1072665948', '52903643', '1032380671', '52378375',
            '52226604', '80241390', '52849113', '1024471606', '53048628', '52382262',
            '52159469', '53067789', '1024509359', '32764527', '63347978', '63489430',
            '1098682989', '37556707', '1010171514', '1023861416', '79844012', '52190948',
            '1024539393', '1073152711', '1023013296', '1014220582', '1015428198', '1030610691',
            '1022340598', '1022415485', '52803785', '37547875', '37752414', '52233576',
            '1019050390', '1073696610', '1014305622', '3276248'
        ];

        return [
            'Usuario' => $usuario,
            'NomUsuario' => $nomUsuario,
            'Perfil' => $perfil,
            'PuedeAdministrar' => in_array($usuario, $adminList),
            'PuedeReversar' => in_array($usuario, $reversarList),
            'PuedeGeneral' => in_array($usuario, $generalList),
            'PuedeCerrar' => $this->inList($perfil, 'DIREC', 'ADMIN', 'DUEPR') || in_array($usuario, $adminList)
        ];
    }

    private function inList($valor, ...$lista)
    {
        foreach ($lista as $item) {
            if (stripos($valor, $item) !== false) return true;
        }
        return false;
    }

    // Funciones equivalentes a funciones.php (DarFecha, DarFechaSQL, DarFechaHora)
    public static function darFecha($fecha)
    {
        if (empty($fecha) || $fecha === '0000-00-00' || substr($fecha, 0, 10) === '0000-00-00') return '';
        try {
            return Carbon::parse($fecha)->format('d/m/Y');
        } catch (\Exception $e) {
            return '';
        }
    }

    public static function darFechaHora($fecha, $tipo = 1)
    {
        if (empty($fecha) || $fecha === '0000-00-00' || substr($fecha, 0, 10) === '0000-00-00') return '';
        try {
            $dt = Carbon::parse($fecha);
            if ($tipo == 1) return $dt->format('d/m/Y');
            return $dt->format('d/m/Y H:i');
        } catch (\Exception $e) {
            return '';
        }
    }

    public static function darFechaSQL($fecha)
    {
        if (empty($fecha)) return null;
        $fechaLimpia = str_replace('-', '/', trim($fecha));
        try {
            if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $fechaLimpia)) {
                return Carbon::createFromFormat('d/m/Y', $fechaLimpia)->format('Y-m-d');
            }
            return Carbon::parse($fecha)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    // =========================================================================
    // 1. PANTALLA PRINCIPAL, FILTRADO Y PAGINACIÓN (20 POR PÁGINA)
    // =========================================================================
    public function index(Request $request)
    {
        $sesion = $this->getSessionData();
        $PuedeAdministrar = $sesion['PuedeAdministrar'];
        $PuedeReversar = $sesion['PuedeReversar'];
        $PuedeGeneral = $sesion['PuedeGeneral'];
        $PuedeCerrar = $sesion['PuedeCerrar'];
        $Usuario = $sesion['Usuario'];
        $NomUsuario = $sesion['NomUsuario'];
        $mTipoRegistro = $this->mTipoRegistro;

        // Listado de Gestiones
        $gestiones = DB::table('calidad.gestiones')->orderBy('NomGestion')->get();

        // Listado de Procesos
        if ($PuedeAdministrar || $PuedeGeneral) {
            $procesos = DB::select("SELECT CodGestion, Colegio, CodProceso, NomProceso FROM calidad.procesos ORDER BY CodGestion, NomProceso");
        } else {
            $procesos = DB::select("SELECT DISTINCT CodGestion, Colegio, CodProceso, NomProceso FROM calidad.procesos WHERE FIND_IN_SET(?, DueProceso) ORDER BY CodGestion, NomProceso", [$Usuario]);
        }

        // Forzar Filtrar = 1 por defecto al entrar (igual que en el script nuevo)
        $filtrar = $request->input('Filtrar', 1);

        $noConformidades = [];
        $totalRegistros = 0;
        $regPorPagina = 20;
        $paginaActual = max(1, (int) $request->input('pagina', 1));
        $totalPaginas = 1;
        $offset = 0;

        if ($filtrar == 1) {
            if ($PuedeAdministrar) {
                $Filtrico = "WHERE NC.NumeroNC";
            } elseif ($PuedeGeneral) {
                $Filtrico = "WHERE NC.Borrada=0";
            } else {
                $Filtrico = "WHERE NC.Borrada=0 AND P.DueProceso LIKE '%" . addslashes($Usuario) . "%'";
            }

            if ($request->filled('FiltroNumeroNC')) {
                $numNC = addslashes($request->input('FiltroNumeroNC'));
                $Filtrico .= " AND FIND_IN_SET(NC.NumeroNC, '{$numNC}')";
            } else {
                if ($request->filled('FiltroCodGestion')) {
                    $Filtrico .= " AND NC.CodGestion = '" . addslashes($request->input('FiltroCodGestion')) . "'";
                }
                if ($request->filled('FiltroTipo')) {
                    $Filtrico .= " AND NC.Tipo = '" . addslashes($request->input('FiltroTipo')) . "'";
                }
                $acc = $request->input('FiltroTipoAcciones');
                if ($acc == 1) {
                    $Filtrico .= " AND (NC.Accion = '' OR NC.Accion IS NULL)";
                } elseif ($acc == 2) {
                    $Filtrico .= " AND (NC.Accion <> '' AND NC.Accion IS NOT NULL)";
                } elseif ($acc == 3) {
                    $Filtrico .= " AND (NC.FCierre = '' OR NC.FCierre IS NULL OR NC.FCierre = '0000-00-00')";
                } elseif ($acc == 4) {
                    $Filtrico .= " AND (NC.FCierre <> '' AND NC.FCierre IS NOT NULL AND NC.FCierre <> '0000-00-00')";
                }
                if ($request->filled('FiltroProceso')) {
                    $Filtrico .= " AND NC.Proceso = '" . addslashes($request->input('FiltroProceso')) . "'";
                }
            }

            $Ordenacioncita = ($request->input('Ordencito') == 1)
                ? " ORDER BY NC.NumeroNC DESC"
                : " ORDER BY NC.FApertura DESC";

            $QueriBase = " FROM calidad.procesos P
                          JOIN calidad.noconformidades NC ON P.CodGestion = NC.CodGestion AND CONCAT(P.Colegio, P.CodProceso) = NC.Proceso
                          LEFT JOIN recursos.emplea E ON NC.Responsable = E.Nit_CCE " . $Filtrico;

            // Conteo total para paginación
            $countRes = DB::select("SELECT COUNT(*) AS total " . $QueriBase);
            $totalRegistros = !empty($countRes) ? (int) $countRes[0]->total : 0;
            $totalPaginas = max(1, (int) ceil($totalRegistros / $regPorPagina));

            if ($paginaActual > $totalPaginas) {
                $paginaActual = $totalPaginas;
            }
            $offset = ($paginaActual - 1) * $regPorPagina;

            $Queri = "SELECT NC.*, CONCAT(E.Nom, ' ', E.Apellido1) AS NomResponsable "
                   . $QueriBase . $Ordenacioncita
                   . " LIMIT " . (int) $offset . ", " . (int) $regPorPagina;

            $noConformidades = DB::select($Queri);
        }

        return view('noconformidades.index', compact(
            'gestiones', 'procesos', 'mTipoRegistro', 'noConformidades',
            'PuedeAdministrar', 'PuedeReversar', 'PuedeGeneral', 'PuedeCerrar', 'NomUsuario',
            'totalRegistros', 'regPorPagina', 'paginaActual', 'totalPaginas', 'offset', 'filtrar'
        ));
    }

    // =========================================================================
    // 2. RETORNAR LOS DATOS DE UNA NO CONFORMIDAD PARA CONSULTA (MODAL)
    // =========================================================================
    public function mostrar(Request $request)
    {
        $sesion = $this->getSessionData();
        $PuedeAdministrar = $sesion['PuedeAdministrar'];
        $mTipoRegistro = $this->mTipoRegistro;
        $numNC = $request->input('NumeroNC');

        $Queri = "SELECT NC.NumeroNC, NC.Tipo, NC.CodGestion, Ges.NomGestion, NC.Proceso, NC.FApertura, NC.DetectadaEn, NC.Descripcion, NC.Correccion,
                         CONCAT(E.Nom,' ',E.Apellido1) AS NomResponsable, NC.Causas, NC.RequiereAC, NC.NumeroNCAccionCorrectiva,
                         NC.Accion, NC.ResultadoRevision, NC.FRevision, NC.MotivoCierre, NC.NuevaAccion, NC.FCierre, NC.RCierre
                  FROM calidad.noconformidades NC
                  LEFT JOIN recursos.emplea E ON NC.Responsable=E.Nit_CCE
                  JOIN calidad.gestiones Ges ON NC.CodGestion=Ges.CodGestion
                  WHERE NC.NumeroNC = ?
                  LIMIT 1";

        $results = DB::select($Queri, [$numNC]);
        if (empty($results)) {
            return response("<b>No existe</b>");
        }
        $Row = (array) $results[0];

        ob_start();
        ?>
        <div class="col-sm-2 panel panel-default">
            <label class="label-xsmall fw-bold">Consecutivo:</label> <?php echo $Row['NumeroNC']; ?>
        </div>
        <div class="col-sm-4 panel panel-default">
            <label class="label-xsmall fw-bold">Responsable de la apertura:</label> <?php echo $Row['NomResponsable']; ?>
        </div>
        <div class="col-sm-6 panel panel-default">
            <label class="label-xsmall fw-bold">Tipo:</label> <?php echo $Row['Tipo'] . ' - ' . ($mTipoRegistro[$Row['Tipo']] ?? ''); ?>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-3 panel panel-default">
            <label class="label-xsmall fw-bold">Gestión:</label> <?php echo $Row['NomGestion']; ?>
        </div>
        <div class="col-sm-3 panel panel-default">
            <label class="label-xsmall fw-bold">Proceso:</label> <?php echo $Row['Proceso']; ?>
        </div>
        <div class="col-sm-3 panel panel-default">
            <label class="label-xsmall fw-bold">Fecha de apertura:</label> <?php echo self::darFecha($Row['FApertura']); ?>
        </div>
        <div class="col-sm-3 panel panel-default">
            <label class="label-xsmall fw-bold">Detectada en:</label> <?php echo $Row['DetectadaEn']; ?>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-12 panel panel-default">
            <label class="label-xsmall fw-bold">Descripción:</label> <?php echo $Row['Descripcion']; ?>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-12 panel panel-default">
            <label class="label-xsmall fw-bold">Relación de actividades:</label> <?php echo $Row['Correccion']; ?>
        </div>
        <?php if ($Row['Tipo'] == 'Corrección'): ?>
            <div class="clearfix"></div>
            <div class="col-sm-8 panel panel-default">
                <label class="label-xsmall fw-bold">Con la acción determinada se garantiza que la falla o posible materialización del riesgo no se presente de nuevo?:</label>
                <?php echo ($Row['RequiereAC'] == 1 ? 'Si' : ($Row['RequiereAC'] == -1 ? 'No' : '')); ?>
            </div>
            <?php if ($Row['RequiereAC'] == -1): ?>
                <div class="col-sm-4 panel panel-default">
                    <label class="label-xsmall fw-bold">ID de la acción correctiva vinculada a esta corrección:</label>
                    <a style="cursor:pointer" onClick="MostrarDivConsultaNoConformidad(<?php echo $Row['NumeroNCAccionCorrectiva']; ?>);" title="Ver <?php echo $Row['NumeroNCAccionCorrectiva']; ?> acción correctiva vinculada a esta corrección"><?php echo $Row['NumeroNC']; ?></a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
        <div class="clearfix"></div>
        <div class="col-sm-9 panel panel-default">
            <label class="label-xsmall fw-bold">Causas o Ventajas:</label> <?php echo $Row['Causas']; ?>
        </div>
        <div class="col-sm-3 panel panel-default">
            <label class="label-xsmall fw-bold">Fecha acordada para la revisión:</label> <?php echo self::darFecha($Row['FRevision']); ?>
        </div>
        <div class="clearfix"></div>
        <?php
        $pdfPath = public_path("Archivos/NoConformidades/" . trim($numNC) . ".pdf");
        if (file_exists($pdfPath)) {
            $pdfUrl = asset("Archivos/NoConformidades/" . trim($numNC) . ".pdf");
            $imgPdf = asset("imagenes/pdf.png");
            echo "<a href='{$pdfUrl}' class='col-sm-12 btn btn-default btn-file' title='Ver Archivo Adjunto' target='_blank'><img src='{$imgPdf}' border='0'>Ver Archivo Adjunto</a>";
        }
        ?>
        <div class="clearfix"></div>
        <?php
        $condBorrada = $PuedeAdministrar ? "" : "AND Borrada=0";
        $QueriNCS = "SELECT NCS.* FROM calidad.ncseguimiento NCS WHERE NumeroNC = ? {$condBorrada} ORDER BY ConsecutivoS";
        $ResultNCS = DB::select($QueriNCS, [$numNC]);

        if (count($ResultNCS) > 0): ?>
            <div class="col-sm-12 panel panel-primary mt-3">
                <h5><b>SEGUIMIENTOS</b></h5>
                <div class="row">
                <?php foreach ($ResultNCS as $rNCS): $RowNCS = (array) $rNCS; ?>
                    <div class="col-sm-6 panel panel-default">
                        <label class="label-xsmall fw-bold">(<?php echo $RowNCS["ConsecutivoS"]; ?>)Acción (Correctiva o Preventiva)</label>
                        <?php echo ($RowNCS['Borrada'] ? ('<b style="background:red">BORRADA ' . $RowNCS['Accion'] . '</b>') : $RowNCS['Accion']); ?>
                    </div>
                    <div class="col-sm-6 panel panel-default">
                        <label class="label-xsmall fw-bold">Resultado Esperado</label> <?php echo $RowNCS['Resultado']; ?>
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-sm-3 panel panel-default">
                        <label class="label-xsmall fw-bold">Fecha Apertura:</label> <?php echo self::darFechaHora($RowNCS["FHSeguimiento"], 3); ?>
                    </div>
                    <div class="col-sm-3 panel panel-default">
                        <label class="label-xsmall fw-bold">Fecha Seguimiento:</label> <?php echo self::darFecha($RowNCS["Fecha"]); ?>
                    </div>
                    <div class="col-sm-4 panel panel-default">
                        <label class="label-xsmall fw-bold">Realizado Por:</label> <?php echo $RowNCS["RealizadoPor"]; ?>
                    </div>
                    <div class="col-sm-2 panel panel-default">
                        <label class="label-xsmall fw-bold">Alcanzado:</label> <?php echo ($RowNCS["Alcanzado"] == 1 ? "Si" : ($RowNCS["Alcanzado"] == -1 ? "No" : "")); ?>
                    </div>
                    <div class="clearfix mb-2 border-bottom"></div>
                <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($Row['FCierre']) && $Row['FCierre'] > '0000-00-00'): ?>
            <div class="col-sm-12 panel panel-primary mt-3">
                <h5><b>DATOS DEL CIERRE</b></h5>
                <div class="row">
                    <div class="col-sm-6 panel panel-default">
                        <label class="label-xsmall fw-bold">Resultados de la revisión de la <i title="Entiéndase la eficacia como el grado en que se realizan las actividades planificadas y se logran los resultados planificados"><u>eficacia</u></i> de las acciones tomadas:</label> <?php echo $Row['ResultadoRevision']; ?>
                    </div>
                    <div class="col-sm-6 panel panel-default">
                        <label class="label-xsmall fw-bold">Motivos del Cierre:</label> <?php echo $Row['MotivoCierre']; ?>
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-sm-6 panel panel-default">
                        <label class="label-xsmall fw-bold">Nueva Acción?:</label> <?php echo $Row['NuevaAccion']; ?>
                    </div>
                    <div class="col-sm-2 panel panel-default">
                        <label class="label-xsmall fw-bold">Fecha del Cierre:</label> <?php echo self::darFecha($Row['FCierre']); ?>
                    </div>
                    <div class="col-sm-4 panel panel-default">
                        <label class="label-xsmall fw-bold">Responsable del Cierre:</label> <?php echo $Row['RCierre']; ?>
                    </div>
                </div>
            </div>
        <?php endif;

        return response(ob_get_clean());
    }

    // =========================================================================
    // 3. RETORNAR LOS DATOS DE UNA NO CONFORMIDAD PARA EDICIÓN (JSON)
    // =========================================================================
    public function editar(Request $request)
    {
        $numNC = $request->input('NumeroNC');
        $Queri = "SELECT NC.*, CONCAT(E.Nom,' ',E.Apellido1) AS NomResponsable, QQueja.IDQueja
                  FROM calidad.noconformidades NC
                  LEFT JOIN (SELECT NumeroNC, MAX(IDQueja) AS IDQueja FROM calidad.quejas WHERE NumeroNC = ?) AS QQueja ON NC.NumeroNC=QQueja.NumeroNC
                  LEFT JOIN recursos.emplea E ON NC.Responsable=E.Nit_CCE
                  WHERE NC.NumeroNC = ?
                  LIMIT 1";

        $results = DB::select($Queri, [$numNC, $numNC]);
        if (!empty($results)) {
            $Row = (array) $results[0];
            $respuesta = [
                'status' => 'OK',
                'CodGestion' => $Row['CodGestion'],
                'Proceso' => $Row['Proceso'],
                'NomResponsable' => $Row['NomResponsable'],
                'Tipo' => $Row['Tipo'],
                'FApertura' => self::darFecha($Row['FApertura']),
                'DetectadaEn' => $Row['DetectadaEn'],
                'DetectadaEnOtra' => ($Row['DetectadaEn'] != 'Quejas' ? $Row['DetectadaEn'] : $Row['IDQueja']),
                'Descripcion' => $Row['Descripcion'],
                'Correccion' => $Row['Correccion'],
                'RequiereAC' => $Row['RequiereAC'],
                'NumeroNCAccionCorrectiva' => $Row['NumeroNCAccionCorrectiva'],
                'Causas' => $Row['Causas'],
                'Accion' => $Row['Accion'],
                'FRevision' => self::darFecha($Row['FRevision']),
                'YaCargado' => (file_exists(public_path("Archivos/NoConformidades/" . trim($numNC) . ".pdf")) ? 1 : 0)
            ];
        } else {
            $respuesta = ['status' => 'Error'];
        }

        return response()->json($respuesta);
    }

    // =========================================================================
    // 4. GRABAR LOS DATOS DE UNA NO CONFORMIDAD (CREAR / ACTUALIZAR)
    // =========================================================================
    public function grabar(Request $request)
    {
        try {
            $sesion = $this->getSessionData();
            $Usuario = $sesion['Usuario'];
            $ipAcceso = $request->ip();

            $detectadaEn = $request->input('DetectadaEn');
            if ($detectadaEn == 'Otra') {
                $detectadaEn = $request->input('DetectadaEnOtra');
            }

            $fAperturaSQL = self::darFechaSQL($request->input('FApertura'));
            $fRevisionSQL = self::darFechaSQL($request->input('FRevision'));
            $numeroNC = $request->input('NumeroNC');

            if ($numeroNC == 'Nuevo') {
                $numeroNC = DB::table('calidad.noconformidades')->insertGetId([
                    'Responsable' => $Usuario,
                    'Tipo' => $request->input('Tipo'),
                    'CodGestion' => $request->input('CodGestion'),
                    'Proceso' => $request->input('Proceso'),
                    'FApertura' => $fAperturaSQL,
                    'DetectadaEn' => $detectadaEn,
                    'Descripcion' => $request->input('Descripcion') ?? '',
                    'Correccion' => $request->input('Correccion') ?? '',
                    'RequiereAC' => $request->input('RequiereAC') ?: 0,
                    'NumeroNCAccionCorrectiva' => $request->input('NumeroNCAccionCorrectiva') ?: 0,
                    'Causas' => $request->input('Causas') ?? '',
                    'Accion' => $request->input('Accion') ?? '',
                    'FRevision' => $fRevisionSQL,
                    'IPApertura' => $ipAcceso,
                    'FHApertura' => now()
                ]);
            } else {
                DB::table('calidad.noconformidades')
                    ->where('NumeroNC', $numeroNC)
                    ->limit(1)
                    ->update([
                        'CodGestion' => $request->input('CodGestion'),
                        'Tipo' => $request->input('Tipo'),
                        'Proceso' => $request->input('Proceso'),
                        'FApertura' => $fAperturaSQL,
                        'DetectadaEn' => $detectadaEn,
                        'Descripcion' => $request->input('Descripcion') ?? '',
                        'Correccion' => $request->input('Correccion') ?? '',
                        'RequiereAC' => $request->input('RequiereAC') ?: 0,
                        'NumeroNCAccionCorrectiva' => $request->input('NumeroNCAccionCorrectiva') ?: 0,
                        'Causas' => $request->input('Causas') ?? '',
                        'Accion' => $request->input('Accion') ?? '',
                        'FRevision' => $fRevisionSQL,
                        'IPApertura' => DB::raw("IF(IPApertura<>'', IPApertura, '{$ipAcceso}')"),
                        'FHApertura' => DB::raw("IF(FHApertura>0, FHApertura, SYSDATE())")
                    ]);
            }

            if ($request->hasFile('FileNoConformidad')) {
                $uploadFolder = public_path('Archivos/NoConformidades');
                if (!file_exists($uploadFolder)) {
                    mkdir($uploadFolder, 0775, true);
                }
                $request->file('FileNoConformidad')->move($uploadFolder, $numeroNC . ".pdf");
            }

            if ($request->input('DetectadaEn') == 'Quejas' && intval($request->input('DetectadaEnOtra'))) {
                DB::table('calidad.quejas')
                    ->where('IDQueja', intval($request->input('DetectadaEnOtra')))
                    ->limit(1)
                    ->update(['NumeroNC' => $numeroNC]);
            }

            return response()->json([
                'status' => 'success',
                'id' => $numeroNC
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    // =========================================================================
    // 5. RETORNAR LOS SEGUIMIENTOS DE UNA NO CONFORMIDAD PARA EDICIÓN
    // =========================================================================
    public function obtenerSeguimientos(Request $request)
    {
        $numNC = $request->input('NumeroNC');
        $ResultNCS = DB::select("SELECT NCS.* FROM calidad.ncseguimiento NCS WHERE Borrada=0 AND NumeroNC = ? ORDER BY ConsecutivoS", [$numNC]);

        $mRegistro = 0;

        ob_start();
        if (count($ResultNCS) > 0) {
            foreach ($ResultNCS as $rNCS) {
                $RowNCS = (array) $rNCS;
                $mRegistro++;
                $this->renderBloqueSeguimiento($mRegistro, $RowNCS);
            }
        } else {
            $mRegistro = 1;
            $RowNCS = ['Accion' => '', 'Resultado' => '', 'Fecha' => '', 'RealizadoPor' => '', 'Alcanzado' => 0];
            $this->renderBloqueSeguimiento($mRegistro, $RowNCS);
        }
        ?>
        <input name="CantSeguimientos" type="hidden" id="CantSeguimientos" value="<?php echo $mRegistro; ?>">
        <?php
        return response(ob_get_clean());
    }

    private function renderBloqueSeguimiento($mRegistro, $RowNCS)
    {
        ?>
        <div class="col-12 panel panel-primary nc-section mb-3" id="DivSeguimiento<?php echo $mRegistro; ?>">
            <input name="Borrada<?php echo $mRegistro; ?>" type="hidden" id="Borrada<?php echo $mRegistro; ?>">
            
            <div class="seguimiento-header d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <span class="badge bg-primary rounded-pill px-3 py-1" id="SpanSeguimiento<?php echo $mRegistro; ?>">
                    Seguimiento <?php echo $mRegistro; ?>
                </span>
                <button type="button" class="action-btn action-danger" id="BotBorrarSeguimiento<?php echo $mRegistro; ?>" onClick="BorrarSeguimiento(this);" title="Borrar este seguimiento">
                    <i class="bi bi-trash3"></i>
                </button>
            </div>

            <div class="row g-3">
                <div class="col-md-6 form-group">
                    <label class="label-xsmall">Objetivo principal del plan de Acción</label>
                    <textarea name="Accion<?php echo $mRegistro; ?>" id="Accion<?php echo $mRegistro; ?>" class="form-control" placeholder="Objetivo principal del plan de Acción" rows="2"><?php echo $RowNCS['Accion']; ?></textarea>
                </div>
                <div class="col-md-6 form-group">
                    <label class="label-xsmall">Resultado Esperado</label>
                    <textarea name="Resultado<?php echo $mRegistro; ?>" id="Resultado<?php echo $mRegistro; ?>" class="form-control" placeholder="Resultado Esperado" rows="2"><?php echo $RowNCS['Resultado']; ?></textarea>
                </div>
                <div class="col-md-3 form-group">
                    <label class="label-xsmall">Fecha Seguimiento:</label>
                    <input type="text" name="Fecha<?php echo $mRegistro; ?>" id="Fecha<?php echo $mRegistro; ?>" class="form-control" placeholder="dd/mm/aaaa" onChange="ValidarFecha(this)" onBlur="ValidarFecha(this)" value="<?php echo self::darFecha($RowNCS['Fecha']); ?>">
                </div>
                <div class="col-md-6 form-group">
                    <label class="label-xsmall">Realizado Por:</label>
                    <input type="text" name="RealizadoPor<?php echo $mRegistro; ?>" id="RealizadoPor<?php echo $mRegistro; ?>" class="form-control" value="<?php echo $RowNCS['RealizadoPor']; ?>">
                </div>
                <div class="col-md-3 form-group">
                    <label class="label-xsmall">Alcanzado:</label>
                    <select name="Alcanzado<?php echo $mRegistro; ?>" class="form-select" id="Alcanzado<?php echo $mRegistro; ?>">
                        <option value="0" selected>...</option>
                        <option value="1" <?php if ($RowNCS["Alcanzado"] == 1) echo "selected"; ?>>SI</option>
                        <option value="-1" <?php if ($RowNCS["Alcanzado"] == -1) echo "selected"; ?>>NO</option>
                    </select>
                </div>
            </div>
        </div>
        <?php
    }

    // =========================================================================
    // 6. GRABAR LOS SEGUIMIENTOS DE UNA NO CONFORMIDAD
    // =========================================================================
    public function grabarSeguimientos(Request $request)
    {
        try {
            $numNC = intval($request->input('NumeroNCSeguimientos'));
            $cant = intval($request->input('CantSeguimientos'));
            $ipAcceso = $request->ip();

            DB::table('calidad.ncseguimiento')
                ->where('NumeroNC', $numNC)
                ->update(['Borrada' => 1]);

            $mFila = 0;
            for ($i = 1; $i <= $cant; $i++) {
                if ($request->input('Borrada' . $i) != 1) {
                    $mFila++;
                    $fechaSQL = self::darFechaSQL($request->input('Fecha' . $i));
                    $accion = $request->input('Accion' . $i) ?? '';
                    $resultado = $request->input('Resultado' . $i) ?? '';
                    $realizadoPor = $request->input('RealizadoPor' . $i) ?? '';
                    $alcanzado = $request->input('Alcanzado' . $i) ?: 0;

                    $Queri = "INSERT INTO calidad.ncseguimiento (NumeroNC, ConsecutivoS, Fecha, Accion, Resultado, RealizadoPor, Alcanzado, IPSeguimiento, FHSeguimiento, Borrada)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, SYSDATE(), 0)
                              ON DUPLICATE KEY UPDATE
                                Fecha = VALUES(Fecha),
                                Accion = VALUES(Accion),
                                Resultado = VALUES(Resultado),
                                RealizadoPor = VALUES(RealizadoPor),
                                Alcanzado = VALUES(Alcanzado),
                                Borrada = 0";

                    DB::statement($Queri, [$numNC, $mFila, $fechaSQL, $accion, $resultado, $realizadoPor, $alcanzado, $ipAcceso]);
                }
            }

            return response()->json([
                'status' => 'success',
                'id' => $numNC
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    // =========================================================================
    // 7. RETORNAR LOS DATOS DE UNA NO CONFORMIDAD PARA CIERRE
    // =========================================================================
    public function obtenerCierre(Request $request)
    {
        $sesion = $this->getSessionData();
        $numNC = $request->input('NumeroNC');

        $Row = DB::table('calidad.noconformidades')
            ->select('NumeroNC', 'ResultadoRevision', 'MotivoCierre', 'NuevaAccion', 'FCierre', 'RCierre')
            ->where('NumeroNC', $numNC)
            ->first();

        if (!$Row) return response("Error");

        $resRev = addslashes((string) $Row->ResultadoRevision);
        $motCie = addslashes((string) $Row->MotivoCierre);
        $nueAcc = addslashes((string) $Row->NuevaAccion);
        $fCierre = self::darFecha($Row->FCierre);
        $rCierre = addslashes($Row->RCierre ? $Row->RCierre : $sesion['NomUsuario']);

        $js = "document.getElementById('ResultadoRevision').value = '{$resRev}';\n";
        $js .= "document.getElementById('MotivoCierre').value = '{$motCie}';\n";
        $js .= "document.getElementById('NuevaAccion').value = '{$nueAcc}';\n";
        $js .= "document.getElementById('FCierre').value = '{$fCierre}';\n";
        $js .= "document.getElementById('RCierre').value = '{$rCierre}';\n";

        return response($js);
    }

    // =========================================================================
    // 8. GRABAR LOS DATOS DE CIERRE DE UNA NO CONFORMIDAD
    // =========================================================================
    public function grabarCierre(Request $request)
    {
        try {
            $sesion = $this->getSessionData();
            $numNC = intval($request->input('NumeroNCCerrar'));

            DB::table('calidad.noconformidades')
                ->where('NumeroNC', $numNC)
                ->limit(1)
                ->update([
                    'ResultadoRevision' => $request->input('ResultadoRevision') ?? '',
                    'MotivoCierre' => $request->input('MotivoCierre') ?? '',
                    'NuevaAccion' => $request->input('NuevaAccion') ?? '',
                    'FCierre' => self::darFechaSQL($request->input('FCierre')),
                    'RCierre' => $sesion['Usuario'],
                    'IPCierre' => $request->ip(),
                    'FHCierre' => now()
                ]);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    // =========================================================================
    // 9. CAMBIO GESTIÓN PROCESO (SELECT DEPENDIENTE)
    // =========================================================================
    public function cambioGestionProceso(Request $request)
    {
        try {
            $sesion = $this->getSessionData();
            $codGestion = $request->input('CodGestion');
            $Usuario = $sesion['Usuario'];

            if ($sesion['PuedeAdministrar'] || $sesion['PuedeGeneral']) {
                $ResultProc = DB::select("SELECT Colegio, CodProceso, NomProceso FROM calidad.procesos WHERE CodGestion = ? ORDER BY NomProceso", [$codGestion]);
            } else {
                $ResultProc = DB::select("SELECT Colegio, CodProceso, NomProceso FROM calidad.procesos WHERE CodGestion = ? AND FIND_IN_SET(?, DueProceso) ORDER BY NomProceso", [$codGestion, $Usuario]);
            }

            $procesos = [];
            foreach ($ResultProc as $rProc) {
                $procesos[] = [
                    'codigo' => $rProc->Colegio . $rProc->CodProceso,
                    'nombre' => $rProc->NomProceso
                ];
            }

            return response()->json([
                'status' => 'success',
                'procesos' => $procesos
            ], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    // =========================================================================
    // 10. REPORTES EXCEL (SHEETJS)
    // =========================================================================
    public function enviarReportes(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $sesion = $this->getSessionData();
        $Usuario = $sesion['Usuario'];

        if ($request->input('TipoReporte') == 'ReporteNoConformidades') {
            try {
                if ($sesion['PuedeAdministrar']) {
                    $Filtrico = "WHERE NC.NumeroNC";
                } elseif ($sesion['PuedeGeneral']) {
                    $Filtrico = "WHERE NC.Borrada=0";
                } else {
                    $uEsc = addslashes($Usuario);
                    $Filtrico = "WHERE NC.Borrada=0 AND (Pr.DueProceso LIKE '%{$uEsc}%' OR NC.Responsable='{$uEsc}')";
                }

                if ($request->filled('ReportProceso')) {
                    $Filtrico .= " AND NC.Proceso = '" . addslashes($request->input('ReportProceso')) . "'";
                }

                $Queri = "SELECT NC.NumeroNC, NC.CodGestion, Pr.NomProceso, NC.Tipo, CONCAT(E.Nom,' ',E.Apellido1) AS NomResponsable,
                                (TIMESTAMPDIFF(DAY, '1900-01-01', NC.FApertura)+2) AS FApertura, NC.DetectadaEn, NC.Descripcion, NC.Correccion,
                                IF(NC.Tipo='Corrección' AND NC.RequiereAC=1,'Si',IF(NC.Tipo='Corrección' AND NC.RequiereAC=-1,'No','')) AS GarantiaNoReinsidencia,
                                NC.NumeroNCAccionCorrectiva, NC.Causas, NC.Accion, NC.ResultadoRevision,
                                (TIMESTAMPDIFF(DAY, '1900-01-01', NC.FRevision)+2) AS FRevision,
                                NC.MotivoCierre, NC.NuevaAccion, (TIMESTAMPDIFF(DAY, '1900-01-01', NC.FCierre)+2) AS FCierre,
                                NC.RCierre, IF(NC.Borrada,'Si','No') AS Borrada
                          FROM calidad.procesos Pr
                          JOIN calidad.noconformidades NC ON Pr.CodGestion=NC.CodGestion AND CONCAT(Pr.Colegio,Pr.CodProceso)=NC.Proceso
                          LEFT JOIN recursos.emplea E ON NC.Responsable=E.Nit_CCE " . $Filtrico;

                $Result = DB::select($Queri);

                $Campos = "rows = prez.map(row => ({NumeroNC:row.NumeroNC," .
                          "CodGestion:row.CodGestion," .
                          "NomProceso:row.NomProceso," .
                          "Tipo:row.Tipo," .
                          "NomResponsable:row.NomResponsable," .
                          "FApertura:(isNaN(parseInt(row.FApertura,10)) ? '' :parseInt(row.FApertura,10))," .
                          "DetectadaEn:row.DetectadaEn," .
                          "Descripcion:row.Descripcion," .
                          "'Actividades (Correccion)':row.Correccion," .
                          "'Acción Garantiza No Reinsidencia':row.GarantiaNoReinsidencia," .
                          "'ID Acción Correctiva':row.NumeroNCAccionCorrectiva," .
                          "'Causas o Ventajas':row.Causas," .
                          "'Objetivo plan Acción':row.Accion," .
                          "ResultadoRevision:row.ResultadoRevision," .
                          "FRevision:(isNaN(parseInt(row.FRevision,10)) ? '' :parseInt(row.FRevision,10))," .
                          "MotivoCierre:row.MotivoCierre," .
                          "NuevaAccion:row.NuevaAccion," .
                          "'Responsable Cierre':row.RCierre," .
                          "FCierre:(isNaN(parseInt(row.FCierre,10)) ? '' :parseInt(row.FCierre,10))," .
                          "Borrada:row.Borrada" .
                          "}));";

                $Formato = "formatColumn(worksheet, 5, 'dd/mm/yyyy');";
                $Formato .= "formatColumn(worksheet, 14, 'dd/mm/yyyy');";
                $Formato .= "formatColumn(worksheet, 18, 'dd/mm/yyyy');";

                $respuestaFinal = [];
                $respuestaFinal[] = [
                    'Codigo' => 'Campos',
                    'Campos' => $Campos,
                    'Formato' => $Formato
                ];

                foreach ($Result as $r) {
                    $Row = (array) $r;
                    $Array = [];
                    foreach ($Row as $Clave => $Valor) {
                        if ($Valor !== null && strlen((string)$Valor) == 10 && strpos((string)$Valor, '-') !== false && self::darFecha($Valor) > '0-0-0') {
                            $Array[$Clave] = str_replace('/', '-', self::darFecha($Valor));
                        } else {
                            $Array[$Clave] = trim((string)$Valor);
                        }
                    }
                    $respuestaFinal[] = $Array;
                }

                return response()->json($respuestaFinal, 200, [], JSON_UNESCAPED_UNICODE);
            } catch (\Exception $e) {
                return response()->json([['Codigo' => 'Error', 'Mensaje' => $e->getMessage()]]);
            }
        }
        return response()->json([]);
    }

    // =========================================================================
    // 11. REVERSAR NO CONFORMIDAD
    // =========================================================================
    public function reversar(Request $request)
    {
        $sesion = $this->getSessionData();
        if (!$sesion['PuedeReversar']) {
            return response()->json(['status' => 'error', 'message' => 'No tiene permisos para reversar no conformidades']);
        }

        try {
            $afectados = DB::table('calidad.noconformidades')
                ->where('NumeroNC', intval($request->input('NumeroNC')))
                ->whereNotNull('FCierre')
                ->limit(1)
                ->update([
                    'FCierre' => null,
                    'RCierre' => null,
                    'IPCierre' => null,
                    'FHCierre' => null,
                    'ResultadoRevision' => '',
                    'MotivoCierre' => '',
                    'NuevaAccion' => ''
                ]);

            if ($afectados == 0) {
                return response()->json(['status' => 'error', 'message' => 'No se pudo reversar la no conformidad. Verifique que esté cerrada.']);
            }
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}