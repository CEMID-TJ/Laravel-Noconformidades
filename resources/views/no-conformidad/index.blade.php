<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#2563eb">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MANEJO DE NO CONFORMIDADES Y MEJORAS</title>

    <!-- 1. LIBRERÍAS EXTERNAS -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/es.js"></script>

    <!-- 2. ESTILOS PERSONALIZADOS -->
    <link rel="stylesheet" href="{{ asset('css/no-conformidad.css') }}?v={{ filemtime(public_path('css/no-conformidad.css')) }}">
    
    <!-- 3. SCRIPTS GLOBALES -->
    <script src="{{ asset('js/global.js') }}"></script>
</head>

<body @if(request('DatoObser')) onLoad="MostrarDatoObser('{{ request('DatoObser') }}', true);" @endif>

@include('partials.alerts')

<nav class="navbar-default fixed-top" role="navigation">
    <div class="navbar-top-bar">
        <a class="navbar-brand-custom" href="{{ url('/noconformidades') }}">
            <span class="brand-icon"><i class="bi bi-clipboard-check"></i></span>
            <span>No Conformidades & Mejoras
                <span class="label-xsmall">Versión 2 &nbsp;·&nbsp; Código PGGC-04-R-01</span>
            </span>
        </a>
        <button class="btn btn-outline-secondary d-md-none btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFiltrosNav"><i class="bi bi-list"></i></button>
    </div>

    <div class="collapse d-md-block" id="collapseFiltrosNav">
        <form method="get" action="{{ url('/noconformidades') }}" class="form_validar" name="FrmFiltrar" id="FrmFiltrar">
            <input name="Filtrar" type="hidden" id="Filtrar" value="1">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-10">
                    <div class="row g-3">
                        <div class="col-6 col-md-2">
                            <select name="FiltroCodGestion" class="form-control" id="FiltroCodGestion">
                                <option value="">Gestión (Todas)</option>
                                @foreach($gestiones as $RowGes)
                                    <option value="{{ $RowGes->CodGestion }}" {{ request('FiltroCodGestion') == $RowGes->CodGestion ? 'selected' : '' }}>{{ $RowGes->NomGestion }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <select name="FiltroProceso" class="form-control" id="FiltroProceso">
                                <option value="">Cualquier proceso</option>
                                @foreach($procesos as $RowProc)
                                    @php $codComp = $RowProc->Colegio . $RowProc->CodProceso; @endphp
                                    <option value="{{ $codComp }}" {{ request('FiltroProceso') == $codComp ? 'selected' : '' }}>{{ $RowProc->CodGestion . ' ' . $RowProc->NomProceso }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <select name="FiltroTipo" class="form-control" id="FiltroTipo">
                                <option value="">Tipo Registro (Todos)</option>
                                @foreach($mTipoRegistro as $Clave => $Valor)
                                    <option value="{{ $Clave }}" {{ request('FiltroTipo') == $Clave ? 'selected' : '' }}>{{ $Clave }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <select name="FiltroTipoAcciones" class="form-control" id="FiltroTipoAcciones">
                                <option value="">Todas las acciones</option>
                                <option value="1" {{ request('FiltroTipoAcciones') == 1 ? 'selected' : '' }}>Sin Acciones</option>
                                <option value="2" {{ request('FiltroTipoAcciones') == 2 ? 'selected' : '' }}>Con Acciones</option>
                                <option value="3" {{ request('FiltroTipoAcciones') == 3 ? 'selected' : '' }}>Por Cerrar</option>
                                <option value="4" {{ request('FiltroTipoAcciones') == 4 ? 'selected' : '' }}>Cerradas</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <select name="Ordencito" class="form-control" id="Ordencito">
                                <option value="2" {{ request('Ordencito', 2) == 2 ? 'selected' : '' }}>Ordenar por Fecha</option>
                                <option value="1" {{ request('Ordencito') == 1 ? 'selected' : '' }}>Ordenar por Número</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <input type="text" name="FiltroNumeroNC" id="FiltroNumeroNC" class="form-control" placeholder="N° reg" value="{{ request('FiltroNumeroNC') }}">
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-2 d-flex justify-content-md-end justify-content-center">
                    <div class="actions-box">
                        <button class="btn-nav" type="button" onClick="ValidarFiltros();" title="Filtrar"><i class="bi bi-search"></i></button>
                        <a onClick="MostrarDivEditarNoConformidad('Nuevo');" class="btn-nav btn-nav-primary" title="Crear"><i class="bi bi-plus-circle"></i></a>
                        <button class="btn-nav" type="button" onClick="MostrarDivReportes();" title="Reportes"><i class="bi bi-file-earmark-excel"></i></button>
                        <a href="{{ url('/noconformidades') }}" class="btn-nav" title="Regresar"><i class="bi bi-house"></i></a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</nav>

<div class="container-main">
    @if($filtrar == 1)
    <div class="row sidenav">
        <div class="col-12">
            <div class="tableFixHead">
                <table class="table table-striped table-bordered small">
                    <thead>
                        <tr>
                            <th width="11%">Fecha / ID solicitud</th>
                            <th width="18%">Responsable Apertura</th>
                            <th width="12%">Proceso</th>
                            <th width="39%">Descripción</th>
                            <th width="10%">Detectada en</th>
                            <th width="10%">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="TBodyNoConformidad">
                        @forelse($noConformidades as $Row)
                            <tr data-id="{{ $Row->NumeroNC }}">
                                <td data-label="Fecha / ID SOLICITUD">
                                    {{ \App\Http\Controllers\NoConformidadController::darFechaHora($Row->FApertura, 1) }}
                                    (<a class="link-modern" style="cursor:pointer" title="Consultar registro" onClick="MostrarDivConsultaNoConformidad({{ $Row->NumeroNC }});">{{ $Row->NumeroNC }}</a>)
                                </td>
                                <td data-label="Responsable">{{ $Row->NomResponsable }}</td>
                                <td data-label="Proceso">{{ $Row->Proceso . ' (' . $Row->CodGestion . ')' }}</td>
                                <td data-label="Descripción" class="desc-cell">
                                    <div class="desc-text">
                                        @if($Row->Borrada) <span class="badge-borrada">BORRADA</span> @endif
                                        <span class="desc-contenido">{{ $Row->Descripcion }}</span>
                                    </div>
                                    <span class="desc-hint"></span>
                                </td>
                                <td data-label="Detectada en">{{ $Row->DetectadaEn }}</td>
                                <td data-label="Acciones">
                                    <div class="acciones-cell">
                                        @if(!empty($Row->FCierre) && $Row->FCierre > '0000-00-00')
                                            <span class="fecha-cierre">{{ \App\Http\Controllers\NoConformidadController::darFecha($Row->FCierre) }}</span>
                                        @endif
                                        <!-- AQUÍ SE ELIMINÓ EL <div class="actions-box"> -->
                                        <a class="action-btn action-view" onClick="MostrarDivConsultaNoConformidad({{ $Row->NumeroNC }});" title="Consultar registro" aria-label="Consultar registro"><i class="bi bi-eye"></i></a>
                                        @if(!empty($Row->FCierre) && $Row->FCierre > '0000-00-00')
                                            @if($PuedeReversar)
                                                <a class="action-btn action-danger" onClick="ReversarNoConformidad({{ $Row->NumeroNC }})" title="Reversar" aria-label="Reversar"><i class="bi bi-arrow-counterclockwise"></i></a>
                                            @endif
                                        @else
                                            <a class="action-btn action-edit" onClick="MostrarDivEditarNoConformidad({{ $Row->NumeroNC }});" title="Editar" aria-label="Editar"><i class="bi bi-pencil-square"></i></a>
                                            <a class="action-btn action-list" onClick="MostrarDivSeguimientos({{ $Row->NumeroNC }});" title="Seguimientos" aria-label="Seguimientos"><i class="bi bi-list-check"></i></a>
                                            @if($PuedeCerrar)
                                                <a class="action-btn action-success" onClick="MostrarDivCerrarNoConformidad({{ $Row->NumeroNC }})" title="Cerrar" aria-label="Cerrar"><i class="bi bi-check2-circle"></i></a>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="sin-resultados" style="text-align:center; padding: 40px;">
                                    <i class="bi bi-inbox" style="font-size:40px; display:block;"></i>
                                    <b>No se encontraron registros con los criterios actuales.</b>
                                    <small>Modifique los filtros o use <b>"N° reg"</b> para buscar por número.</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- 6. PAGINACIÓN -->
            @if(isset($totalRegistros) && $totalRegistros > 0)
            @php
                $urlPagina = function ($n) { return request()->fullUrlWithQuery(['Filtrar' => 1, 'pagina' => $n]); };
                $rangoPag = 2; $inicioPag = max(1, $paginaActual - $rangoPag); $finPag = min($totalPaginas, $paginaActual + $rangoPag);
            @endphp
            <div class="nc-pagination">
                <div class="nc-pagination-info">Mostrando {{ $offset + 1 }}&ndash;{{ min($offset + $regPorPagina, $totalRegistros) }} de {{ $totalRegistros }} registros</div>
                <div class="nc-pagination-controls">
                    <a class="page-btn {{ $paginaActual <= 1 ? 'disabled' : '' }}" href="{{ $paginaActual > 1 ? $urlPagina($paginaActual - 1) : '#' }}">&laquo; Anterior</a>
                    @if($inicioPag > 1)
                        <a class="page-btn" href="{{ $urlPagina(1) }}">1</a>
                        @if($inicioPag > 2) <span class="page-ellipsis">&hellip;</span> @endif
                    @endif
                    @for($p = $inicioPag; $p <= $finPag; $p++)
                        <a class="page-btn {{ $p == $paginaActual ? 'active' : '' }}" href="{{ $urlPagina($p) }}">{{ $p }}</a>
                    @endfor
                    @if($finPag < $totalPaginas)
                        @if($finPag < $totalPaginas - 1) <span class="page-ellipsis">&hellip;</span> @endif
                        <a class="page-btn" href="{{ $urlPagina($totalPaginas) }}">{{ $totalPaginas }}</a>
                    @endif
                    <a class="page-btn {{ $paginaActual >= $totalPaginas ? 'disabled' : '' }}" href="{{ $paginaActual < $totalPaginas ? $urlPagina($paginaActual + 1) : '#' }}">Siguiente &raquo;</a>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>

<!-- ========================================== -->
<!-- MODALES (Sin cambios)                       -->
<!-- ========================================== -->
<!-- (El resto de tus modales de consulta, edición, seguimientos, cierre y reportes se mantienen exactamente igual a como los tenías) -->

<!-- MODAL CONSULTA -->
<div class="modal fade" id="ModalConsultaNoConformidad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" style="max-width:98%">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="bi bi-eye"></i> Consulta Registro</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><div id="ModalConsultaNoConformidadBody">...</div></div>
            <div class="modal-footer"><button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i> Cerrar</button></div>
        </div>
    </div>
</div>

<!-- MODAL EDITAR/CREAR -->
<div class="modal fade" id="ModalEditarNoConformidad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" style="max-width:98%">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="TituloModalNoConformidad"><i class="bi bi-pencil-square"></i> Editar / Crear No Conformidad</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="DivBodyNoConformidad">
                <form method="post" enctype="multipart/form-data" class="form_validar" name="FrmNoConformidad" id="FrmNoConformidad" autocomplete="off">
                    <input name="TipoGrabar" type="hidden" id="TipoGrabar">
                    <input name="TipoModificar" type="hidden" id="TipoModificar">
                    <input name="NumeroNC" type="hidden" id="NumeroNC">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="nc-box">
                                <div class="form-group"><label>Responsable</label><input type="text" name="Responsable" id="Responsable" class="form-control" readonly></div>
                                <div class="nc-row">
                                    <div class="nc-cell nc-cell-4"><label>Tipo</label><select name="Tipo" class="form-control" id="Tipo" onChange="CambioTipo()"><option value="">...</option>@foreach($mTipoRegistro as $Clave => $Valor)<option value="{{ $Clave }}">{{ $Clave }}</option>@endforeach</select></div>
                                    <div class="nc-cell nc-cell-8"><label>Gestión</label><select name="CodGestion" class="form-control" id="CodGestion" onChange="CambioGestionProceso();"><option value=""></option>@foreach($gestiones as $RowGes)<option value="{{ $RowGes->CodGestion }}">{{ $RowGes->NomGestion }}</option>@endforeach</select></div>
                                </div>
                                <div class="form-group"><label>Proceso</label><select name="Proceso" class="form-control" id="Proceso"><option value="" selected>...</option></select></div>
                                <div class="form-group"><label>Fecha de apertura</label><input type="text" name="FApertura" id="FApertura" class="form-control"></div>
                                <div class="nc-row">
                                    <div class="nc-cell nc-cell-8"><label>Detectada en</label><select name="DetectadaEn" class="form-control" id="DetectadaEn" onChange="ValidarDetectadaEn(this.value);"><option value="">...</option><option value="Auditoría Interna">Auditoría Interna</option><option value="Auditoría Externa">Auditoría Externa</option><option value="Control de proceso">Control de proceso</option><option value="Quejas">Quejas</option><option value="Otra">Otra</option></select></div>
                                    <div class="nc-cell nc-cell-4" id="DivDetectadaEnOtra"><label>Cual?</label><input name="DetectadaEnOtra" class="form-control" id="DetectadaEnOtra"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="nc-box">
                                <div class="form-group"><label>Descripción</label><textarea name="Descripcion" id="Descripcion" class="form-control" rows="3"></textarea></div>
                                <div class="form-group"><label>Relación de actividades</label><textarea name="Correccion" id="Correccion" class="form-control" rows="3"></textarea></div>
                                <div class="nc-row">
                                    <div class="nc-cell nc-cell-8" id="DivRequiereAC"><label>¿Se garantiza que la falla no se presente de nuevo?</label><select name="RequiereAC" class="form-control" id="RequiereAC" onChange="CambioTipo();"><option value="">...</option><option value="1">Si</option><option value="-1">No</option></select></div>
                                    <div class="nc-cell nc-cell-4" id="DivNumeroNCAccionCorrectiva"><label>ID acción correctiva</label><input name="NumeroNCAccionCorrectiva" class="form-control" id="NumeroNCAccionCorrectiva" maxlength="11"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="nc-box">
                                <div class="form-group"><label>Causas o Ventajas</label><textarea name="Causas" id="Causas" class="form-control" rows="3"></textarea></div>
                                <div class="form-group"><label>Objetivo principal</label><textarea name="Accion" id="Accion" class="form-control" rows="3"></textarea></div>
                                <div class="form-group"><label>Fecha acordada revisión</label><input type="text" name="FRevision" id="FRevision" class="form-control"></div>
                                
                                <div class="form-group">
                                <label>Documento Adjunto</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <label for="FileNoConformidad" class="btn btn-upload-file flex-grow-1 d-flex align-items-center justify-content-center gap-2 py-2 mb-0" id="LabelFileNoConformidad">
                                            <i class="bi bi-file-earmark-pdf-fill fs-5"></i>
                                            <span id="SpanFileNoConformidad">Seleccionar archivo PDF</span>
                                        </label>
                                        <button type="button" class="action-btn action-danger flex-shrink-0 d-none" id="BtnEliminarArchivo" data-existente="0" onClick="EliminarArchivoNoConformidad();" title="Eliminar PDF adjunto">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                    <input type="file" id="FileNoConformidad" name="FileNoConformidad" class="d-none" accept="application/pdf" onChange="ValidarArchivo(this)">
                                    <small class="text-muted d-block mt-1" style="font-size: 11px;">Máximo 4MB. Si ya hay uno cargado, se reemplazará.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer" id="BotsNoConformidad">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i> Cerrar</button>
                <button type="button" class="btn btn-success" id="BtnAceptarNoConformidad" onClick="GrabarNoConformidad();"><i class="bi bi-check2"></i> Aceptar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL SEGUIMIENTOS -->
<div class="modal fade" id="ModalSeguimientos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" style="max-width:98%">
        <div class="modal-content">
            <div class="modal-header"><h4 class="modal-title"><i class="bi bi-list-check"></i> Seguimientos</h4><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="DivBodySeguimientos">
                <form method="post" enctype="multipart/form-data" name="FrmSeguimientos" id="FrmSeguimientos" autocomplete="off">
                    <input name="TipoGrabar" type="hidden" id="TipoGrabar">
                    <input name="TipoModificar" type="hidden" id="TipoModificar">
                    <input name="NumeroNCSeguimientos" type="hidden" id="NumeroNCSeguimientos">
                    <div class="col-12" id="DivSeguimientos"></div>
                </form>
            </div>
            <div class="modal-footer" id="BotsSeguimientos">
                <button type="button" class="btn btn-secondary" onClick="AgregarSeguimiento();"><i class="bi bi-plus-lg"></i> Otro Seguimiento</button>
                <div style="flex:1"></div>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i> Cerrar</button>
                <button type="button" class="btn btn-success" onClick="GrabarSeguimientos();"><i class="bi bi-check2"></i> Aceptar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CERRAR -->
<div class="modal fade" id="ModalCerrarNoConformidad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" style="max-width:98%">
        <div class="modal-content">
            <div class="modal-header"><h3 class="modal-title"><i class="bi bi-check2-circle"></i> Cierre del Registro</h3><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="DivBodyCerrarNoConformidad">
                <form method="post" enctype="multipart/form-data" name="FrmCerrarNoConformidad" id="FrmCerrarNoConformidad" autocomplete="off">
                    <input name="TipoGrabar" type="hidden" id="TipoGrabar">
                    <input name="TipoModificar" type="hidden" id="TipoModificar">
                    <input name="NumeroNCCerrar" type="hidden" id="NumeroNCCerrar">
                    <div class="row g-3">
                        <div class="col-md-6 form-group"><label>Resultados de la revisión</label><textarea name="ResultadoRevision" id="ResultadoRevision" class="form-control" rows="3"></textarea></div>
                        <div class="col-md-6 form-group"><label>Motivos del Cierre</label><textarea name="MotivoCierre" id="MotivoCierre" class="form-control" rows="3"></textarea></div>
                        <div class="col-md-6 form-group"><label>Nueva Acción</label><textarea name="NuevaAccion" id="NuevaAccion" class="form-control" rows="3"></textarea></div>
                        <div class="col-md-2 form-group"><label>Fecha del Cierre</label><input type="text" name="FCierre" id="FCierre" class="form-control"></div>
                        <div class="col-md-4 form-group"><label>Responsable del Cierre</label><input type="text" name="RCierre" id="RCierre" class="form-control"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer" id="BotsCerrarNoConformidad">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i> Cerrar</button>
                <button type="button" class="btn btn-success" onClick="GrabarCerrarNoConformidad();"><i class="bi bi-check2"></i> Aceptar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL REPORTES -->
<div class="modal fade" id="ModalReportes" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" style="width:98%;max-width:900px">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="bi bi-file-earmark-excel"></i> Reportes</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="ModalReportesBody">
                <form method="post" enctype="multipart/form-data" name="FrmReportes" id="FrmReportes" autocomplete="off" target="_blank" style="width:100%">
                    <input name="TipoGrabar" type="hidden" id="TipoGrabar">
                    <input name="TipoModificar" type="hidden" id="TipoModificar">
                    <div class="nc-modal-center"><div class="nc-box">
                        <div class="form-group"><label>Tipo Reporte</label><select name="TipoReporte" class="form-control" id="TipoReporte" onChange="CambioTipoReporte(this);"><option value="">...</option><option value="ReporteNoConformidades">Todos los registros</option></select></div>
                        <div class="form-group" id="DivFechasReporte"><label>Proceso</label><select name="ReportProceso" id="ReportProceso" class="form-control"><option value="">Todos los procesos</option>@foreach($procesos as $RowProc)@php $codComp = $RowProc->Colegio . $RowProc->CodProceso; @endphp<option value="{{ $codComp }}">{{ $RowProc->CodGestion . ' ' . $RowProc->NomProceso }}</option>@endforeach</select></div>
                    </div></div>
                </form>
            </div>
            <div class="modal-footer" id="BotsReportes">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i> Cerrar</button>
                <button type="button" class="btn btn-success" id="BtnGenerarReporte" onClick="EnviarReportes();"><i class="bi bi-file-earmark-arrow-down"></i> Generar</button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- SCRIPTS (Mismo JS que ya tenías)           -->
<!-- ========================================== -->
<script>
    // ... (Pega aquí todo el bloque <script> que ya tenías, no requiere cambios) ...
</script>
</body>
</html>