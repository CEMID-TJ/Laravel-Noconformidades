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

<!-- Inclusión de la Alerta Global -->
@include('partials.alerts')

<!-- 4. NAVBAR Y FILTROS -->
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

<!-- 5. CONTENIDO PRINCIPAL (TABLA) -->
<div class="container-main">
    @if($filtrar == 1)
    <div class="row sidenav">
        <div class="col-12">
            <div class="tableFixHead">
                <table class="table table-striped table-bordered small">
                    <thead>
                        <tr>
                            <th width="11%">Fecha / ID solicitud</th>
                            <th width="15%">Responsable Apertura</th>
                            <th width="10%">Proceso</th>
                            <th width="36%">Descripción</th>
                            <th width="10%">Detectada en</th>
                            <th width="18%">Acciones</th>
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
<!-- 7. MODALES ESPECÍFICOS DEL MÓDULO           -->
<!-- ========================================== -->

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
                                
                                <!-- INPUT DE ARCHIVO NATIVO DE BOOTSTRAP -->
                                <div class="form-group">
                                <label>Documento Adjunto</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <!-- Botón de Bootstrap nativo -->
                                        <label for="FileNoConformidad" class="btn btn-upload-file flex-grow-1 d-flex align-items-center justify-content-center gap-2 py-2 mb-0" id="LabelFileNoConformidad">
                                            <i class="bi bi-file-earmark-pdf-fill fs-5"></i>
                                            <span id="SpanFileNoConformidad">Seleccionar archivo PDF</span>
                                        </label>

                                        <!-- Basurita para descartar la selección o eliminar el PDF guardado -->
                                        <button type="button" class="action-btn action-danger flex-shrink-0 d-none" id="BtnEliminarArchivo" data-existente="0" onClick="EliminarArchivoNoConformidad();" title="Eliminar PDF adjunto">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Input real (oculto con la clase d-none) -->
                                    <input type="file" id="FileNoConformidad" name="FileNoConformidad" class="d-none" accept="application/pdf" onChange="ValidarArchivo(this)">
                                    
                                    <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                        Máximo 4MB. Si ya hay uno cargado, se reemplazará.
                                    </small>
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
<!-- 8. SCRIPTS ESPECÍFICOS DEL MÓDULO          -->
<!-- ========================================== -->
<script>
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    $(function () {
        $('#DivDetectadaEnOtra, #DivRequiereAC, #DivNumeroNCAccionCorrectiva').hide();

        $.datepicker.regional['es'] = {
            closeText: 'Cerrar', prevText: '&#x3c;Ant', nextText: 'Sig&#x3e;', currentText: 'Hoy',
            monthNames: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
            monthNamesShort: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
            dayNames: ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'],
            dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sáb'],
            dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'], weekHeader: 'Sm', dateFormat: 'dd/mm/yy', firstDay: 1, isRTL: false
        };
        $.datepicker.setDefaults($.datepicker.regional['es']);
        $("#FApertura, #FRevision, #FCierre").datepicker({ dateFormat: 'dd-mm-yy', showOn: 'focus' });

        $('#FiltroCodGestion, #FiltroProceso, #FiltroTipo, #FiltroTipoAcciones, #Ordencito').select2({ language: 'es', width: '100%' });
        $('#CodGestion, #Proceso, #Tipo, #DetectadaEn, #RequiereAC').select2({ language: 'es', dropdownParent: $('#ModalEditarNoConformidad'), width: '100%' });
        $('#TipoReporte, #ReportProceso').select2({ language: 'es', dropdownParent: $('#ModalReportes'), width: '100%' });

        // En tablet y móvil, tocar la tarjeta abre el detalle completo (salvo en botones o enlaces)
        $(document).on('click', '#TBodyNoConformidad tr[data-id]', function (e) {
            if (!window.matchMedia('(max-width: 991px)').matches) { return; }
            if ($(e.target).closest('a, button, input, select, textarea').length) { return; }
            var sel = window.getSelection();
            if (sel && !sel.isCollapsed && this.contains(sel.anchorNode)) { return; }
            var id = $(this).data('id');
            if (id) { MostrarDivConsultaNoConformidad(id); }
        });
    });

    function ValidarFiltros() { document.FrmFiltrar.submit(); }

    // Devuelve el botón de archivo a su estado inicial (o al PDF ya guardado)
    function restaurarBotonArchivo() {
        var label = document.getElementById('LabelFileNoConformidad');
        var span = document.getElementById('SpanFileNoConformidad');
        var btn = document.getElementById('BtnEliminarArchivo');

        if (btn.dataset.existente === '1') {
            span.innerHTML = 'Reemplazar PDF existente';
            label.classList.add('archivo-cargado');
            btn.classList.remove('d-none');
        } else {
            span.innerHTML = 'Seleccionar archivo PDF';
            label.classList.remove('archivo-cargado');
            btn.classList.add('d-none');
        }
    }

    // Valida el archivo nativo de Bootstrap al seleccionarlo
    function ValidarArchivo(input) {
        var file = input.files[0];

        if (!file) {
            restaurarBotonArchivo();
            return;
        }

        if (file.type !== "application/pdf") {
            MostrarDatoObser("¡Error! Solo se admiten archivos en formato PDF");
            input.value = '';
            restaurarBotonArchivo();
            return;
        }
        if (file.size > 4 * 1024 * 1024) {
            MostrarDatoObser("¡Error! El archivo supera el tamaño máximo permitido (4MB)");
            input.value = '';
            restaurarBotonArchivo();
            return;
        }

        var nombre = file.name;
        if (nombre.length > 35) nombre = nombre.substring(0, 32) + '...';
        document.getElementById('SpanFileNoConformidad').innerHTML = nombre;
        document.getElementById('LabelFileNoConformidad').classList.add('archivo-cargado');
        document.getElementById('BtnEliminarArchivo').classList.remove('d-none');
    }

    // Descarta la selección local o elimina el PDF ya guardado en el servidor
    function EliminarArchivoNoConformidad() {
        var input = document.getElementById('FileNoConformidad');
        var btn = document.getElementById('BtnEliminarArchivo');

        // Si hay un archivo recién seleccionado, solo se descarta la selección
        if (input.files && input.files.length > 0) {
            input.value = '';
            restaurarBotonArchivo();
            return;
        }

        // Si no hay selección nueva, se elimina el PDF guardado
        if (btn.dataset.existente !== '1') { return; }

        var numeroNC = document.getElementById('NumeroNC').value;
        if (!numeroNC || numeroNC === 'Nuevo') { return; }
        if (!confirm('¿Eliminar el PDF adjunto? Esta acción no se puede deshacer.')) { return; }

        btn.disabled = true;
        $.ajax({
            type: 'POST', url: "{{ route('noconformidades.archivo.delete') }}", data: { NumeroNC: numeroNC }, dataType: 'json', cache: false
        }).done(function (response) {
            if (response.status === 'success') {
                btn.dataset.existente = '0';
                restaurarBotonArchivo();
                MostrarDatoObser('PDF eliminado correctamente.', true);
            } else {
                MostrarDatoObser(response.message || 'No se pudo eliminar el PDF.');
            }
        }).fail(function () {
            MostrarDatoObser('Error de conexión al eliminar el PDF.');
        }).always(function () {
            btn.disabled = false;
        });
    }

    function ScrollAlPrimerError(contenedor) {
        var $err = $(contenedor).find('.alert-danger').filter(':visible').first();
        if ($err.length) {
            var dom = $err[0];
            try { dom.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch (e) { dom.scrollIntoView(); }
            setTimeout(function () { try { dom.focus(); } catch (e) {} }, 250);
        }
    }

    function ValidarDetectadaEn(Valorcito) {
        if (Valorcito == "Otra" || Valorcito == "Quejas") {
            document.FrmNoConformidad.DetectadaEnOtra.value = ''; $('#DivDetectadaEnOtra').show();
        } else { $('#DivDetectadaEnOtra').hide(); }
    }

    function MostrarDivConsultaNoConformidad(mNumeroNC) {
        $("#ModalConsultaNoConformidadBody").load("{{ url('/noconformidades/mostrar') }}/" + mNumeroNC, function () {
            $("#ModalConsultaNoConformidad").modal('show');
        });
    }

    function MostrarDivEditarNoConformidad(mNumeroNC) {
        document.FrmNoConformidad.reset();
        document.getElementById('NumeroNC').value = mNumeroNC;
        $("#FrmNoConformidad").find("input:text,select,textarea").removeClass("alert-danger");
        $("#BotsNoConformidad").show();
        
        // Reseteamos el estado del archivo (selección local y PDF existente)
        document.getElementById('FileNoConformidad').value = '';
        document.getElementById('BtnEliminarArchivo').dataset.existente = '0';
        restaurarBotonArchivo();

        if (mNumeroNC == 'Nuevo') {
            $("#TituloModalNoConformidad").html('<i class="bi bi-plus-circle"></i> Crear Nueva No Conformidad');
            $("#BtnAceptarNoConformidad").html('<i class="bi bi-check2"></i> Crear Registro');

            document.getElementById('Responsable').value = '{{ $NomUsuario }}';
            document.getElementById('FApertura').value = '{{ date("d/m/Y") }}';
            $('#DivDetectadaEnOtra, #DivRequiereAC, #DivNumeroNCAccionCorrectiva').hide();
            document.getElementById('Proceso').length = 1;
            $('#CodGestion, #Proceso, #Tipo, #DetectadaEn, #RequiereAC').val('').trigger('change');
            $("#ModalEditarNoConformidad").modal('show');
        } else {
            $("#TituloModalNoConformidad").html('<i class="bi bi-pencil-square"></i> Editar No Conformidad #' + mNumeroNC);
            $("#BtnAceptarNoConformidad").html('<i class="bi bi-save"></i> Guardar Cambios');

            $.ajax({
                type: "GET", url: "{{ url('/noconformidades/editar') }}/" + mNumeroNC, dataType: "json", cache: false
            }).done(function (data) {
                if (data.status === "Error") { MostrarDatoObser("Se presentó un error"); return false; }
                $('#CodGestion').val(data.CodGestion).trigger('change'); CambioGestionProceso(data.Proceso);
                document.getElementById('Responsable').value = data.NomResponsable;
                $('#Tipo').val(data.Tipo).trigger('change');
                document.getElementById('FApertura').value = data.FApertura;
                var detectadaVal = data.DetectadaEn;
                if ($('#DetectadaEn option[value="' + detectadaVal + '"]').length === 0 && detectadaVal !== '') { detectadaVal = 'Otra'; }
                $('#DetectadaEn').val(detectadaVal).trigger('change'); ValidarDetectadaEn(detectadaVal);
                document.getElementById('DetectadaEnOtra').value = data.DetectadaEnOtra || '';
                document.getElementById('Descripcion').value = data.Descripcion || '';
                document.getElementById('Correccion').value = data.Correccion || '';
                $('#RequiereAC').val(data.RequiereAC).trigger('change'); CambioTipo();
                document.getElementById('NumeroNCAccionCorrectiva').value = data.NumeroNCAccionCorrectiva || '';
                document.getElementById('Causas').value = data.Causas || '';
                document.getElementById('Accion').value = data.Accion || '';
                document.getElementById('FRevision').value = data.FRevision || '';
                
                // Si ya hay PDF, habilitar reemplazo y eliminación
                document.getElementById('BtnEliminarArchivo').dataset.existente = (data.YaCargado === 1) ? '1' : '0';
                restaurarBotonArchivo();
                
                $("#ModalEditarNoConformidad").modal('show');
            });
        }
    }

    function GrabarNoConformidad() {
        MostrarDatoObser('Procesando');
        var mNumeroNC = document.FrmNoConformidad.NumeroNC.value;
        $("#BotsNoConformidad").hide();
        var mRetorno = true;
        var $camposObligatorios = $("#Responsable, #Tipo, #CodGestion, #Proceso, #FApertura, #DetectadaEn, #Descripcion, #FRevision");
        $camposObligatorios.each(function () {
            $(this).removeClass("alert-danger");
            if (!$(this).val() || $(this).val().trim() === '') { mRetorno = false; $(this).addClass("alert-danger"); }
        });
        if ($("#DetectadaEn").val() === 'Otra' || $("#DetectadaEn").val() === 'Quejas') {
            if (!$("#DetectadaEnOtra").val() || $("#DetectadaEnOtra").val().trim() === '') { mRetorno = false; $("#DetectadaEnOtra").addClass("alert-danger"); }
            else { $("#DetectadaEnOtra").removeClass("alert-danger"); }
        } else { $("#DetectadaEnOtra").removeClass("alert-danger"); }
        
        if ($("#Tipo").val() === 'Corrección' && $("#RequiereAC").val() === '-1') {
            if (!$("#NumeroNCAccionCorrectiva").val() || $("#NumeroNCAccionCorrectiva").val().trim() === '') { mRetorno = false; $("#NumeroNCAccionCorrectiva").addClass("alert-danger"); }
            else { $("#NumeroNCAccionCorrectiva").removeClass("alert-danger"); }
        }
        
        if (mRetorno) {
            document.FrmNoConformidad.TipoGrabar.value = 'GrabarNoConformidad';
            var f = new FormData(document.getElementById('FrmNoConformidad'));
            $.ajax({
                url: "{{ route('noconformidades.store') }}", type: 'post', cache: false, data: f, dataType: "json", processData: false, contentType: false
            }).done(function (response) {
                if (response.status === 'success') {
                    MostrarDatoObser("Los datos se grabaron.", true);
                    $("#FiltroNumeroNC").val(response.id);
                    setTimeout(function () { document.FrmFiltrar.submit(); }, 500);
                } else { $("#BotsNoConformidad").show(); MostrarDatoObser(response.message || "No se pudo guardar el registro."); }
            }).fail(function () { $("#BotsNoConformidad").show(); MostrarDatoObser("Error de conexión o formato inválido."); });
        } else {
            $("#BotsNoConformidad").show(); MostrarDatoObser('<i class="bi bi-exclamation-triangle"></i> Complete los campos resaltados en rojo.'); ScrollAlPrimerError('#FrmNoConformidad');
        }
    }

    function MostrarDivSeguimientos(mNumeroNCSeguimientos) {
        document.FrmSeguimientos.reset();
        document.getElementById('NumeroNCSeguimientos').value = mNumeroNCSeguimientos;
        $("#BotsSeguimientos").show(); $("#FrmSeguimientos").find("input:text,select,textarea").removeClass("alert-danger");
        $("#DivSeguimientos").load("{{ url('/noconformidades/seguimientos') }}/" + mNumeroNCSeguimientos, function () {
            $("#ModalSeguimientos").modal('show');
            $("#DivSeguimientos input[id^='Fecha']").datepicker({ dateFormat: 'dd-mm-yy', showOn: 'focus' });
        });
    }

    function BorrarSeguimiento(Obj) {
        var btn = Obj.closest('button') || Obj.parentElement;
        var mID = btn.id.replace("BotBorrarSeguimiento", "");
        document.getElementById('Borrada' + mID).value = 1; $("#DivSeguimiento" + mID).hide();
    }

    function AgregarSeguimiento() {
        var mRegNew = parseInt(document.getElementById('CantSeguimientos').value, 10);
        if (isNaN(mRegNew)) { mRegNew = 1; } else { mRegNew++; }
        document.getElementById('CantSeguimientos').value = mRegNew;

        var $nuevoBloque = $('#DivSeguimiento1').clone();
        $nuevoBloque.find("input,select,a,button,textarea,div,span").each(function () {
            $(this).attr({
                'id': function (_, id) { return (id ? id.replace('1', mRegNew) : null); },
                'name': function (_, name) { return (name ? name.replace('1', mRegNew) : null); },
                'checked': false
            });
            if ($(this).is('input,select,textarea')) { $(this).val(''); }
        });

        $nuevoBloque.find("input[id^='Fecha']").removeClass('hasDatepicker').removeData('datepicker').unbind();
        
        // CORRECCIÓN AQUÍ: Agregamos la palabra "Seguimiento"
        $nuevoBloque.find('#SpanSeguimiento' + mRegNew).text('Seguimiento ' + mRegNew);
        
        $nuevoBloque.attr("id", "DivSeguimiento" + mRegNew);
        $nuevoBloque.show(); $nuevoBloque.appendTo('#DivSeguimientos');
        $("#Fecha" + mRegNew).datepicker({ dateFormat: 'dd/mm/yy', showOn: 'focus' });
    }

    // --- NUEVOS EFECTOS HOVER ---
    // Hover sobre el botón de la basura (Pinta la tarjeta de rojo)
    $(document).on('mouseenter', '.btn-borrar-seguimiento', function() {
        $(this).closest('.seguimiento-card').addClass('card-eliminar-hover');
    }).on('mouseleave', '.btn-borrar-seguimiento', function() {
        $(this).closest('.seguimiento-card').removeClass('card-eliminar-hover');
    });

    function GrabarSeguimientos() {
        MostrarDatoObser('Procesando');
        var mNumeroNCSeguimientos = document.getElementById('NumeroNCSeguimientos').value;
        $("#BotsSeguimientos").hide();
        var mRetorno = true, mAlMenosUno = false;
        var mCantSeguimientos = document.getElementById('CantSeguimientos').value;
        
        for (var NumSeguimiento = 1; NumSeguimiento <= mCantSeguimientos; NumSeguimiento++) {
            if (document.getElementById('Borrada' + NumSeguimiento) && document.getElementById('Borrada' + NumSeguimiento).value != 1) {
                mAlMenosUno = true;
                $("#Accion" + NumSeguimiento + ", #Resultado" + NumSeguimiento + ", #Fecha" + NumSeguimiento + ", #RealizadoPor" + NumSeguimiento).each(function () {
                    $(this).removeClass("alert-danger");
                    if (!$(this).val() || $(this).val().trim() === '') { mRetorno = false; $(this).addClass("alert-danger"); }
                });
            }
        }
        if (!mAlMenosUno) { mRetorno = false; }

        if (mRetorno) {
            document.FrmSeguimientos.TipoGrabar.value = 'Seguimientos';
            var f = new FormData(document.getElementById('FrmSeguimientos'));
            $.ajax({
                url: "{{ route('noconformidades.seguimientos.save') }}", type: 'post', cache: false, data: f, dataType: "json", processData: false, contentType: false
            }).done(function (response) {
                if (response.status === 'success') {
                    MostrarDatoObser("Los datos se grabaron.", true); setTimeout(function () { location.reload(); }, 500);
                } else { $("#BotsSeguimientos").show(); MostrarDatoObser(response.message || "Error al guardar."); }
            });
        } else {
            $("#BotsSeguimientos").show();
            if (!mAlMenosUno) { MostrarDatoObser('<i class="bi bi-exclamation-triangle"></i> Debe registrar al menos un seguimiento.'); }
            else { MostrarDatoObser('<i class="bi bi-exclamation-triangle"></i> Complete los campos resaltados en rojo.'); ScrollAlPrimerError('#FrmSeguimientos'); }
        }
    }

    function MostrarDivCerrarNoConformidad(mNumeroNCCerrar) {
        document.FrmCerrarNoConformidad.reset();
        document.getElementById('NumeroNCCerrar').value = mNumeroNCCerrar;
        $("#BotsCerrarNoConformidad").show(); $("#FrmCerrarNoConformidad").find("input:text,select,textarea").removeClass("alert-danger");
        $.ajax({
            type: "GET", url: "{{ url('/noconformidades/cierre') }}/" + mNumeroNCCerrar, cache: false
        }).done(function (html) {
            if (html == "Error") { MostrarDatoObser("Se presentó un error"); return false; }
            else { eval(html); $("#ModalCerrarNoConformidad").modal('show'); }
        });
    }

    function GrabarCerrarNoConformidad() {
        MostrarDatoObser('Procesando');
        var mNumeroNCCerrar = document.getElementById('NumeroNCCerrar').value;
        $("#BotsCerrarNoConformidad").hide(); var mRetorno = true;
        $("#ResultadoRevision, #MotivoCierre, #NuevaAccion, #FCierre, #RCierre").each(function () {
            $(this).removeClass("alert-danger");
            if (!$(this).val() || $(this).val().trim() === '') { mRetorno = false; $(this).addClass("alert-danger"); }
        });
        if (mRetorno) {
            document.FrmCerrarNoConformidad.TipoGrabar.value = 'CerrarNoConformidad';
            var f = new FormData(document.getElementById('FrmCerrarNoConformidad'));
            $.ajax({
                url: "{{ route('noconformidades.cierre.save') }}", type: 'post', cache: false, data: f, dataType: "json", processData: false, contentType: false
            }).done(function (response) {
                if (response.status === 'success') {
                    MostrarDatoObser("Los datos se grabaron.", true); setTimeout(function () { location.reload(); }, 500);
                } else { $("#BotsCerrarNoConformidad").show(); MostrarDatoObser(response.message || "Error al guardar."); }
            });
        } else {
            $("#BotsCerrarNoConformidad").show(); MostrarDatoObser('<i class="bi bi-exclamation-triangle"></i> Complete los campos resaltados en rojo.'); ScrollAlPrimerError('#FrmCerrarNoConformidad');
        }
    }

    var ultimaPeticionProceso = 0;
    var procesoPendiente = '';
    function CambioGestionProceso(mProceso) {
        if (typeof mProceso === 'undefined') mProceso = '';
        var sel = document.getElementById('Proceso');
        var mCodGestion = document.getElementById('CodGestion').value;
        if (mProceso) procesoPendiente = mProceso;
        var seleccionado = procesoPendiente || sel.value;
        var id = ++ultimaPeticionProceso;
        sel.length = 1;
        if (!mCodGestion) { procesoPendiente = ''; $('#Proceso').val('').trigger('change'); return; }
        $.ajax({
            type: "GET", url: "{{ url('/noconformidades/procesos') }}/" + mCodGestion, dataType: "json", cache: false
        }).done(function (r) {
            if (id !== ultimaPeticionProceso) return;
            if (r.status !== 'success') { MostrarDatoObser(r.message || "Se presentó un error"); return; }
            sel.length = 1;
            r.procesos.forEach(p => sel.add(new Option(p.nombre, p.codigo)));
            if (seleccionado && Array.from(sel.options).some(o => o.value === seleccionado)) { sel.value = seleccionado; }
            procesoPendiente = ''; $('#Proceso').trigger('change');
        });
    }

    function CambioTipo() {
        var mCodTipo = document.getElementById('Tipo').value;
        var mRequiereAC = document.getElementById('RequiereAC').value;
        if (mCodTipo == 'Corrección') {
            $("#DivRequiereAC").show();
            if (mRequiereAC == '-1') { $("#DivNumeroNCAccionCorrectiva").show(); }
            else { $("#DivNumeroNCAccionCorrectiva").hide(); }
        } else { $("#DivRequiereAC, #DivNumeroNCAccionCorrectiva").hide(); }
    }

    function MostrarDivReportes() { $("#ModalReportes").modal('show'); }

    function EnviarReportes() {
        var mRetorno = true;
        $("#TipoReporte").removeClass("alert-danger");
        if (!$("#TipoReporte").val()) { mRetorno = false; $("#TipoReporte").addClass("alert-danger"); }
        if (!mRetorno) { MostrarDatoObser('<i class="bi bi-exclamation-triangle"></i> Seleccione el tipo de reporte.'); ScrollAlPrimerError('#FrmReportes'); return; }
        
        MostrarDatoObser('Procesando');
        var $btnGenerar = $("#BtnGenerarReporte"); $btnGenerar.prop('disabled', true);
        (async () => {
            try {
                var url = "{{ route('noconformidades.reportes') }}?TipoReporte=" + encodeURIComponent($("#TipoReporte").val()) + "&ReportProceso=" + encodeURIComponent($("#ReportProceso").val() || '');
                const response = await fetch(url);
                if (!response.ok) { throw new Error('Error HTTP ' + response.status); }
                const raw_data = await response.json();
                if (!Array.isArray(raw_data)) { MostrarDatoObser('<i class="bi bi-exclamation-triangle"></i> ' + (raw_data.error || 'No se pudo generar el reporte.')); return; }
                if (raw_data.length < 2 || !raw_data[0] || !raw_data[0]['Campos']) { MostrarDatoObser('<i class="bi bi-exclamation-triangle"></i> No hay datos para el reporte.'); return; }
                let mCampos = raw_data[0]; raw_data.shift();
                const prez = raw_data; var rows = [];
                if (prez.length === 0) { MostrarDatoObser('<i class="bi bi-exclamation-triangle"></i> No hay datos.'); return; }
                eval(mCampos['Campos']);
                const worksheet = XLSX.utils.json_to_sheet(rows);
                eval(mCampos['Formato']);
                const workbook = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(workbook, worksheet, "Datos");
                XLSX.writeFile(workbook, "datos.xlsx", { compression: true });
                MostrarDatoObser('<i class="bi bi-check-circle"></i> Reporte generado correctamente.', true);
            } catch (err) { MostrarDatoObser('<i class="bi bi-exclamation-triangle"></i> Error al generar reporte: ' + err.message);
            } finally { $btnGenerar.prop('disabled', false); }
        })();
    }

    function formatColumn(worksheet, col, fmt) {
        if (!worksheet['!ref']) return;
        const range = XLSX.utils.decode_range(worksheet['!ref']);
        for (let row = range.s.r + 1; row <= range.e.r; ++row) {
            const ref = XLSX.utils.encode_cell({ c: col, r: row });
            if (worksheet[ref] && worksheet[ref].t === 'n') { worksheet[ref].z = fmt; }
        }
    }

    function CambioTipoReporte(Obj) {
        $("#DivFechasReporte").hide();
        if (['ReporteNoConformidades'].indexOf(Obj.value) >= 0) { $("#DivFechasReporte").show(); }
    }

    function ReversarNoConformidad(mNumeroNC) {
        if (confirm('¿Está seguro que desea reversar esta no conformidad? Esto borrará la fecha de cierre y permitirá editarla nuevamente.')) {
            MostrarDatoObser('Procesando');
            $.ajax({
                type: "POST", url: "{{ route('noconformidades.reversar') }}", data: { TipoGrabar: 'ReversarNoConformidad', NumeroNC: mNumeroNC }, dataType: 'json', cache: false
            }).done(function (response) {
                if (response.status === 'success') {
                    MostrarDatoObser("No conformidad reversada exitosamente.", true); setTimeout(function () { location.reload(); }, 1000);
                } else { MostrarDatoObser("Error al reversar: " + (response.message || '')); }
            });
        }
    }
</script>
</body>
</html>