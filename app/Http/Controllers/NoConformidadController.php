<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NoConformidadController extends Controller
{
    // 1. CARGA LA VISTA PRINCIPAL (Reemplaza la parte inferior de tu archivo original)
    public function index(Request $request)
    {
        // Consultas básicas para los selects de filtros
        $gestiones = DB::table('calidad.gestiones')->orderBy('NomGestion')->get();
        
        // Simulación de sesión (Ajustar cuando migres tu login a Laravel)
        $usuarioActual = "1054540722"; // Johan Rodriguez
        $puedeAdministrar = true; 

        $queryProcesos = DB::table('calidad.procesos');
        if (!$puedeAdministrar) {
            $queryProcesos->whereRaw("FIND_IN_SET(?, DueProceso)", [$usuarioActual]);
        }
        $procesos = $queryProcesos->orderBy('CodGestion')->orderBy('NomProceso')->get();

        // Consulta principal de No Conformidades
        $query = DB::table('calidad.noconformidades as NC')
            ->join('calidad.procesos as P', function($join) {
                $join->on('P.CodGestion', '=', 'NC.CodGestion')
                     ->on(DB::raw('CONCAT(P.Colegio, P.CodProceso)'), '=', 'NC.Proceso');
            })
            ->leftJoin('recursos.emplea as E', 'NC.Responsable', '=', 'E.Nit_CCE')
            ->select('NC.*', DB::raw("CONCAT(E.Nom,' ',E.Apellido1) AS NomResponsable"));

        // Lógica de Filtros
        if ($request->filled('Filtrar')) {
            if ($request->filled('FiltroNumeroNC')) {
                $query->whereIn('NC.NumeroNC', explode(',', $request->FiltroNumeroNC));
            } else {
                if ($request->filled('FiltroCodGestion')) $query->where('NC.CodGestion', $request->FiltroCodGestion);
                if ($request->filled('FiltroProceso')) $query->where('NC.Proceso', $request->FiltroProceso);
                if ($request->filled('FiltroTipo')) $query->where('NC.Tipo', $request->FiltroTipo);
                
                switch ($request->FiltroTipoAcciones) {
                    case 1: $query->where(fn($q) => $q->whereNull('NC.Accion')->orWhere('NC.Accion', '')); break;
                    case 2: $query->whereNotNull('NC.Accion')->where('NC.Accion', '!=', ''); break;
                    case 3: $query->where(fn($q) => $q->whereNull('NC.FCierre')->orWhere('NC.FCierre', '')); break;
                    case 4: $query->whereNotNull('NC.FCierre')->where('NC.FCierre', '!=', ''); break;
                }
            }
        }

        // Ordenamiento
        $orden = $request->Ordencito == 1 ? 'NC.NumeroNC' : 'NC.FApertura';
        $noConformidades = $query->orderByDesc($orden)->get();

        return view('noconformidades.index', compact('gestiones', 'procesos', 'noConformidades'));
    }

    // 2. ENDPOINT PARA EL SELECT2 DINÁMICO (Reemplaza a JorCambioGestionProceso)
    public function getProcesos($codGestion)
    {
        $procesos = DB::table('calidad.procesos')
            ->where('CodGestion', $codGestion)
            ->orderBy('NomProceso')
            ->get()
            ->map(function($proc) {
                return [
                    'codigo' => $proc->Colegio . $proc->CodProceso,
                    'nombre' => $proc->NomProceso
                ];
            });

        return response()->json(['status' => 'success', 'procesos' => $procesos], 200, [], JSON_UNESCAPED_UNICODE);
    }

    // 3. ENDPOINT PARA CARGAR DATOS AL EDITAR (Reemplaza a Ajax1JorA1NoConformidad)
    public function edit($id)
    {
        $nc = DB::table('calidad.noconformidades as NC')
            ->leftJoin(DB::raw("(SELECT NumeroNC, MAX(IDQueja) AS IDQueja FROM calidad.quejas GROUP BY NumeroNC) as QQueja"), 'NC.NumeroNC', '=', 'QQueja.NumeroNC')
            ->leftJoin('recursos.emplea as E', 'NC.Responsable', '=', 'E.Nit_CCE')
            ->select('NC.*', DB::raw("CONCAT(E.Nom,' ',E.Apellido1) AS NomResponsable"), 'QQueja.IDQueja')
            ->where('NC.NumeroNC', $id)
            ->first();

        if (!$nc) return response()->json(['status' => 'Error']);

        return response()->json([
            'status' => 'OK',
            'CodGestion' => $nc->CodGestion,
            'Proceso' => $nc->Proceso,
            'NomResponsable' => $nc->NomResponsable,
            'Tipo' => $nc->Tipo,
            'FApertura' => $nc->FApertura ? Carbon::parse($nc->FApertura)->format('d/m/Y') : '',
            'DetectadaEn' => $nc->DetectadaEn,
            'DetectadaEnOtra' => ($nc->DetectadaEn !== 'Quejas') ? $nc->DetectadaEn : $nc->IDQueja,
            'Descripcion' => $nc->Descripcion,
            'Correccion' => $nc->Correccion,
            'RequiereAC' => $nc->RequiereAC,
            'NumeroNCAccionCorrectiva' => $nc->NumeroNCAccionCorrectiva,
            'Causas' => $nc->Causas,
            'Accion' => $nc->Accion,
            'FRevision' => $nc->FRevision ? Carbon::parse($nc->FRevision)->format('d/m/Y') : '',
            'YaCargado' => 0 // Ajustar con Storage::exists() cuando migres los archivos
        ]);
    }

    // 4. ENDPOINT PARA GUARDAR O ACTUALIZAR (Reemplaza a JorGrabarNoConformidad)
    public function store(Request $request)
    {
        try {
            $datos = $request->only([
                'CodGestion', 'Proceso', 'Tipo', 'DetectadaEn', 'Descripcion', 
                'Correccion', 'RequiereAC', 'NumeroNCAccionCorrectiva', 'Causas', 'Accion'
            ]);

            // Fechas a formato MySQL
            $datos['FApertura'] = $request->filled('FApertura') ? Carbon::createFromFormat('d/m/Y', $request->FApertura)->format('Y-m-d') : null;
            $datos['FRevision'] = $request->filled('FRevision') ? Carbon::createFromFormat('d/m/Y', $request->FRevision)->format('Y-m-d') : null;

            if ($request->DetectadaEn == 'Otra') {
                $datos['DetectadaEn'] = $request->DetectadaEnOtra;
            }

            if ($request->NumeroNC == 'Nuevo') {
                $datos['Responsable'] = "1054540722"; // Usuario quemado temporalmente
                $datos['IPApertura'] = $request->ip();
                $datos['FHApertura'] = now();
                
                $id = DB::table('calidad.noconformidades')->insertGetId($datos);
            } else {
                $id = $request->NumeroNC;
                $datos['IPApertura'] = DB::raw("IF(IPApertura<>'',IPApertura,'".$request->ip()."')");
                $datos['FHApertura'] = DB::raw("IF(FHApertura>0,FHApertura,SYSDATE())");
                
                DB::table('calidad.noconformidades')->where('NumeroNC', $id)->update($datos);
            }

            // Aquí iría el bloque de subida de archivos y envío de correos (Lo podemos pulir después)

            return response()->json(['status' => 'success', 'id' => $id]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    // 5. ENDPOINT PARA REVERSAR (Reemplaza a JorReversarNoConformidad)
    public function reversar(Request $request)
    {
        try {
            $afectados = DB::table('calidad.noconformidades')
                ->where('NumeroNC', $request->NumeroNC)
                ->whereNotNull('FCierre')
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
                return response()->json(['status' => 'error', 'message' => 'No se pudo reversar. Verifique que esté cerrada.']);
            }
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}