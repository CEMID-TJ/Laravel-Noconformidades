<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>MANEJO DE NO CONFORMIDADES Y MEJORAS</title>

<!-- JQuery & JQuery UI (Con respaldo CDN por si no están en public/librerias) -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

<!-- Bootstrap 5.3.3 & SheetJS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

<!-- Funciones locales (si existe en public/funciones.js) + Respaldo nativo -->
<script src="{{ asset('funciones.js') }}"></script>
<script>
if (typeof ValidarFecha !== 'function') {
    function ValidarFecha(obj) { return true; }
}
if (typeof ValidarNumeros !== 'function') {
    function ValidarNumeros(obj) { obj.value = obj.value.replace(/[^0-9]/g, ''); }
}
</script>

<!-- Select2 + Tema Bootstrap 5 + Idioma Español -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/es.js"></script>

<style>
:root {
    --primary-dark: #1A2B4C;
    --accent-gold: #EAA315;
    --bg-light: #F4F7F6;
}
body {
    background-color: var(--bg-light);
    color: #333;
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    padding-top: 20px !important; 
}
/* Tarjeta Fija de Filtros COMPACTA */
.filter-card {
    background: white;
    border-radius: 8px;
    border-left: 4px solid var(--accent-gold);
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    padding: 0.3rem 1rem !important; 
    margin-bottom: 0.4rem !important;
    z-index: 1020;
}
.filter-label {
    font-size: 0.65rem;
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 0 !important; 
    line-height: 1.2;
}
/* Reducir la altura de inputs y botones a 28px */
.filter-card .form-select-sm, 
.filter-card .form-control-sm,
.filter-card .btn {
    min-height: 28px !important;
    height: 28px !important;
    padding-top: 0.1rem !important;
    padding-bottom: 0.1rem !important;
    font-size: 0.75rem !important;
    display: flex;
    align-items: center;
}
/* Reducir la altura del buscador Select2 */
.filter-card .select2-container--bootstrap-5 .select2-selection {
    min-height: 28px !important;
    height: 28px !important;
    padding: 0.1rem 0.5rem !important;
    font-size: 0.75rem !important;
}
.filter-card .select2-container--bootstrap-5 .select2-selection__rendered {
    line-height: 24px !important;
}

.input-dashed {
    border: 1.5px dashed #adb5bd !important;
    border-radius: 8px;
    color: #6c757d;
}

/* Solo afecta a los modales */
#ModalEditarNoConformidad .select2-container { 
    width: 100% !important; 
}

.select2-is-invalid .select2-selection { 
    border-color: #dc3545 !important; 
    background-color: #fff8f8 !important; 
}

/* Botones Personalizados */
.btn-dark-blue { background-color: var(--primary-dark); color: white; border-radius: 8px; }
.btn-dark-blue:hover { background-color: #121f3a; color: white; }
.btn-gold { background-color: var(--accent-gold); color: white; border-radius: 8px; }
.btn-gold:hover { background-color: #d19112; color: white; }
.btn-outline-gray { border: 1px solid #dee2e6; color: #495057; background: white; border-radius: 8px; }
/* Etiquetas (Badges) */
.badge-version { background-color: #e9ecef; color: #495057; font-weight: 600; padding: 0.35em 0.8em; border-radius: 20px; font-size: 0.75rem; }
.badge-code { color: var(--accent-gold); font-weight: 700; font-size: 0.8rem; }

/* Tabla con Encabezado Fijo, Scroll Horizontal y ALTURA FORZADA al 100% */
.tableFixHead {
    height: calc(100vh - 115px);
    overflow-y: auto;
    overflow-x: auto;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    width: 100%;
}
/* Forzar que la tabla respete los porcentajes y no se aplaste */
.tableFixHead .table {
    width: 100%;
    min-width: 900px;
    table-layout: fixed; 
    margin-bottom: 0;
}

.tableFixHead thead th {
    position: sticky;
    top: 0;
    background-color: var(--primary-dark) !important;
    color: white !important;
    z-index: 3;
    font-weight: 600;
    font-size: 0.75rem; 
    padding: 0.6rem 0.8rem;
    border-bottom: none;
}
.tableFixHead thead th:first-child { border-top-left-radius: 8px; }
.tableFixHead thead th:last-child { border-top-right-radius: 8px; }

.table tbody td {
    padding: 0.4rem 0.8rem;
    vertical-align: middle;
    font-size: 0.75rem; 
    border-bottom: 1px solid #f0f4f8;
    word-wrap: break-word;
}

/* Corrección del corte de texto en la descripción */
.desc-limitada {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.4;
    max-height: 2.8em;
    white-space: normal;
}
/* Ajuste Select2 al nuevo diseño */
.select2-container--bootstrap-5 .select2-selection { border-radius: 8px; font-size: 0.85rem; border-color: #dee2e6; padding: 0.15rem; }
.ui-autocomplete { z-index: 2147483647 !important; }
.myAlert-top { position: fixed; top: 15px; left: 50%; transform: translateX(-50%); width: 90%; max-width: 600px; z-index: 1055; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border-radius: 6px; }
</style>

<script type="text/javascript">
$.ajaxSetup({
    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
});

$(function(){
    $('#DivDetectadaEnOtra,#DivRequiereAC,#DivNumeroNCAccionCorrectiva').hide();
    $.datepicker.regional['es'] = {
        closeText: 'Cerrar',
        prevText: '&#x3c;Ant',
        nextText: 'Sig&#x3e;',
        currentText: 'Hoy',
        monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
        monthNamesShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
        dayNamesShort: ['Dom', 'Lun', 'Mar', 'Mié', 'Juv', 'Vie', 'Sáb'],
        dayNamesMin: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'],
        weekHeader: 'Sm',
        dateFormat: 'dd/mm/yy',
        firstDay: 1,
        isRTL: false,
        showMonthAfterYear: false,
        yearSuffix: ''
    };
    $.datepicker.setDefaults($.datepicker.regional['es']);
    $("#FApertura,#FRevision,#FCierre").datepicker({ dateFormat: 'dd/mm/yy' });

    // Aplicar Select2 (Autocompletado al escribir) a los filtros de la barra superior
    $('#FiltroCodGestion, #FiltroProceso, #FiltroTipo, #FiltroTipoAcciones').select2({
        theme: 'bootstrap-5',
        language: "es",
        width: 'resolve'
    });

    // Aplicar Select2 a los selects dentro de la ventana de "Crear/Editar"
    $('#CodGestion, #Proceso, #DetectadaEn, #Tipo').select2({
        theme: 'bootstrap-5',
        language: "es",
        dropdownParent: $('#ModalEditarNoConformidad'),
        width: '100%' 
    });
});

function MostrarDatoObser(msg, esError = false){
    let mDiv = $("#DatoObser");
    mDiv.hide();
    if(msg){
        mDiv.html((esError ? "⚠️ " : "✅ ") + msg);
    }
    if(esError){
        mDiv.removeClass("alert-success").addClass("alert-danger fw-bold");
    } else {
        mDiv.removeClass("alert-danger fw-bold").addClass("alert-success");
    }
    mDiv.show();
    setTimeout(function(){ mDiv.hide(); }, 3500); 
}

function ValidarDetectadaEn(Valorcito){
    if(Valorcito == "Otra" || Valorcito == "Quejas"){
        document.FrmNoConformidad.DetectadaEnOtra.value='';
        $('#DivDetectadaEnOtra').show();
    }else{
        $('#DivDetectadaEnOtra').hide();
    }
}

function MostrarDivConsultaNoConformidad(mNumeroNC){
    $("#ModalConsultaNoConformidadBody").load("{{ url('/noconformidades/mostrar') }}?NumeroNC=" + mNumeroNC, function(){
        $("#ModalConsultaNoConformidad").modal('show');
    });
}

function MostrarDivEditarNoConformidad(mNumeroNC){
    document.FrmNoConformidad.reset();
    document.getElementById('NumeroNC').value = mNumeroNC;
    $("#FrmNoConformidad").find("input:text,select,textarea").removeClass("alert-danger is-invalid");
    $("#BotsNoConformidad").show();
    
    if(mNumeroNC == 'Nuevo'){
        document.getElementById('Responsable').value = '{{ $NomUsuario }}';
        document.getElementById('FApertura').value = '{{ date("d/m/Y") }}';
        document.getElementById('FileNoConformidad').dataset.yacargado = 0;
        
        $('#DivDetectadaEnOtra, #DivRequiereAC, #DivNumeroNCAccionCorrectiva').hide();
        document.getElementById('Proceso').length = 1;
        $('#CodGestion, #Proceso, #DetectadaEn, #Tipo, #RequiereAC').trigger('change');

        $("#ModalEditarNoConformidad").modal('show');
    } else {
        $.ajax({
            type: "GET",
            url: "{{ url('/noconformidades/editar') }}",
            data: { NumeroNC: mNumeroNC },
            dataType: "json",
            cache: false,
        }).done(function(data){
            if(data.status === "Error" || data === "Error"){
                MostrarDatoObser("Se presentó un error", true);
                return false;
            } else {
                document.getElementById('CodGestion').value = data.CodGestion;
                CambioGestionProceso(data.Proceso);
                
                document.getElementById('Responsable').value = data.NomResponsable;
                document.getElementById('Tipo').value = data.Tipo;
                document.getElementById('FApertura').value = data.FApertura;
                
                let detectadaEn = document.getElementById('DetectadaEn');
                detectadaEn.value = data.DetectadaEn;
                if(detectadaEn.selectedIndex <= 0 && data.DetectadaEn !== ''){
                    detectadaEn.value = 'Otra';
                }
                ValidarDetectadaEn(detectadaEn.value);
                
                document.getElementById('DetectadaEnOtra').value = data.DetectadaEnOtra;
                document.getElementById('Descripcion').value = data.Descripcion;
                document.getElementById('Correccion').value = data.Correccion;
                document.getElementById('RequiereAC').value = data.RequiereAC;
                
                CambioTipo();
                
                document.getElementById('NumeroNCAccionCorrectiva').value = data.NumeroNCAccionCorrectiva;
                document.getElementById('Causas').value = data.Causas;
                document.getElementById('Accion').value = data.Accion;
                document.getElementById('FRevision').value = data.FRevision;
                document.getElementById('FileNoConformidad').dataset.yacargado = data.YaCargado;
                
                $('#CodGestion, #DetectadaEn, #Tipo').trigger('change');

                $("#ModalEditarNoConformidad").modal('show');
            }
        });
    }
}

function GrabarNoConformidad(){
    MostrarDatoObser('Procesando...', false);
    mNumeroNC = document.FrmNoConformidad.NumeroNC.value;
    $("#BotsNoConformidad").hide();
    mRetorno = true;
    
    // Validación de campos obligatorios
    $("#Responsable,#Tipo,#CodGestion,#Proceso,#FApertura,#DetectadaEn,#Descripcion,#FRevision").each(function(){
        if($(this).val()){
            $(this).removeClass("is-invalid alert-danger"); 
            if($(this).hasClass('select2-hidden-accessible')) {
                $(this).next('.select2-container').removeClass('select2-is-invalid');
            }
        } else {
            mRetorno = false;
            $(this).addClass("is-invalid");
            if($(this).hasClass('select2-hidden-accessible')) {
                $(this).next('.select2-container').addClass('select2-is-invalid'); 
            }
        }
    });
    
    if(mRetorno){
        var input = document.getElementById("FileNoConformidad");
        if(input.files.length > 0){
            if(!(input.files[0].type == "application/pdf")){
                $("#BotsNoConformidad").show();
                MostrarDatoObser("¡Error! Recuerde que solo se admiten archivos en formato PDF", true);
                return false;
            }
        }
    }
    
    if(mRetorno){
        document.FrmNoConformidad.TipoGrabar.value = 'GrabarNoConformidad';
        var f = new FormData(document.getElementById('FrmNoConformidad'));
        
        $.ajax({
            url: "{{ url('/noconformidades/grabar') }}",
            type: 'post',
            cache: false,
            data: f,
            dataType: "json",
            processData: false,
            contentType: false
        }).done(function(response){
            if(response.status === 'success'){
                MostrarDatoObser("Los datos se grabaron correctamente.", false);
                $("#FiltroNumeroNC").val(response.id);
                setTimeout(function(){ document.FrmFiltrar.submit(); }, 800);
            } else {
                $("#BotsNoConformidad").show();
                MostrarDatoObser("Error: " + (response.message || "No se pudo guardar"), true);
            }
        }).fail(function(){
            $("#BotsNoConformidad").show();
            MostrarDatoObser("Error de conexión o formato inválido en el servidor.", true);
        });
    } else {
        $("#BotsNoConformidad").show();
        MostrarDatoObser("Los campos resaltados en rojo son obligatorios.", true);
    }
}

function MostrarDivSeguimientos(mNumeroNCSeguimientos){
    document.FrmSeguimientos.reset();
    document.getElementById('NumeroNCSeguimientos').value = mNumeroNCSeguimientos;
    $("#BotsSeguimientos").show();
    $("#FrmSeguimientos").find("input:text,select,textarea").removeClass("alert-danger");
    $("#DivSeguimientos").load("{{ url('/noconformidades/seguimientos') }}?NumeroNC=" + mNumeroNCSeguimientos, function(){
        $("#ModalSeguimientos").modal('show');
        $("#DivSeguimientos input[id^='Fecha']").datepicker({ dateFormat: 'dd/mm/yy' });
    });
}

function BorrarSeguimiento(Obj){
    mID = Obj.parentElement.id.replace("BotBorrarSeguimiento", "");
    document.getElementById('Borrada' + mID).value = 1;
    $("#DivSeguimiento" + mID).hide();
}

function AgregarSeguimiento(){
    mRegNew = parseInt(document.getElementById('CantSeguimientos').value, 10);
    if(isNaN(mRegNew)){
        mRegNew = 1;
    } else {
        mRegNew++;
    }
    document.getElementById('CantSeguimientos').value = mRegNew;
    $('#DivSeguimiento1').clone().find("input,select,a,button,textarea,div,span").each(function(){
        $(this).attr({
            'id': function(_, id) { return (id ? id.replace(1, mRegNew) : null); },
            'name': function(_, name) { return (name ? name.replace(1, mRegNew) : null); },
            'checked': false
        });
        $(this).val(($(this).is(':checkbox') ? 1 : ''));
        $(this).removeClass('hasDatepicker');
    }).end().appendTo('#DivSeguimientos');
    $('#SpanSeguimiento' + mRegNew).html(mRegNew);
    $('#DivSeguimientos div[id^="DivSeguimiento1"]:last').show();
    $('#DivSeguimientos div[id^="DivSeguimiento1"]:last').attr("id", "DivSeguimiento" + mRegNew);
    $("#Fecha" + mRegNew).datepicker({ dateFormat: 'dd/mm/yy' });
}

function GrabarSeguimientos(){
    MostrarDatoObser('Procesando');
    mNumeroNCSeguimientos = document.getElementById('NumeroNCSeguimientos').value;
    $("#BotsSeguimientos").hide();
    mRetorno = true;
    mCantSeguimientos = document.getElementById('CantSeguimientos').value;
    for(NumSeguimiento = 1; NumSeguimiento <= mCantSeguimientos; NumSeguimiento++){
        if(document.getElementById('Borrada' + NumSeguimiento) && document.getElementById('Borrada' + NumSeguimiento).value != 1){
            $("#Accion" + NumSeguimiento + ",#Resultado" + NumSeguimiento + ",#Fecha" + NumSeguimiento + ",#RealizadoPor" + NumSeguimiento).each(function(){
                if($(this).val()){
                    $(this).removeClass("alert-danger is-invalid");
                } else {
                    mRetorno = false;
                    $(this).addClass("alert-danger is-invalid");
                }
            });
        }
    }
    if(mRetorno){
        document.FrmSeguimientos.TipoGrabar.value = 'Seguimientos';
        var f = new FormData(document.getElementById('FrmSeguimientos'));
        $.ajax({
            url: "{{ url('/noconformidades/seguimientos') }}",
            type: 'post',
            cache: false,
            data: f,
            dataType: "json",
            processData: false,
            contentType: false
        }).done(function(response){
            if(response.status === 'success'){
                MostrarDatoObser("Los datos se grabaron.", false);
                setTimeout(function(){ location.reload(); }, 500);
            } else {
                $("#BotsSeguimientos").show();
                MostrarDatoObser(response.message || "Error al guardar", true);
            }
        });
    } else {
        $("#BotsSeguimientos").show();
        MostrarDatoObser("Los campos resaltados son obligatorios.", true);
    }
}

function MostrarDivCerrarNoConformidad(mNumeroNCCerrar){
    document.FrmCerrarNoConformidad.reset();
    document.getElementById('NumeroNCCerrar').value = mNumeroNCCerrar;
    $("#BotsCerrarNoConformidad").show();
    $("#FrmCerrarNoConformidad").find("input:text,select,textarea").removeClass("alert-danger is-invalid");
    $.ajax({
        type: "GET",
        url: "{{ url('/noconformidades/cierre') }}",
        data: { NumeroNC: mNumeroNCCerrar },
        cache: false,
    }).done(function(html){
        if(html == "Error"){
            MostrarDatoObser("Se presentó un error", true);
            return false;
        } else {
            eval(html);
            $("#ModalCerrarNoConformidad").modal('show');
        }
    });
}

function GrabarCerrarNoConformidad(){
    MostrarDatoObser('Procesando');
    mNumeroNCCerrar = document.getElementById('NumeroNCCerrar').value;
    $("#BotsCerrarNoConformidad").hide();
    mRetorno = true;
    $("#ResultadoRevision,#MotivoCierre,#NuevaAccion,#FCierre,#RCierre").each(function(){
        if($(this).val()){
            $(this).removeClass("alert-danger is-invalid");
        } else {
            mRetorno = false;
            $(this).addClass("alert-danger is-invalid");
        }
    });
    if(mRetorno){
        document.FrmCerrarNoConformidad.TipoGrabar.value = 'CerrarNoConformidad';
        var f = new FormData(document.getElementById('FrmCerrarNoConformidad'));
        $.ajax({
            url: "{{ url('/noconformidades/cierre') }}",
            type: 'post',
            cache: false,
            data: f,
            dataType: "json",
            processData: false,
            contentType: false
        }).done(function(response){
            if(response.status === 'success'){
                MostrarDatoObser("Los datos se grabaron.", false);
                setTimeout(function(){ location.reload(); }, 500);
            } else {
                $("#BotsCerrarNoConformidad").show();
                MostrarDatoObser(response.message || "Error", true);
            }
        });
    } else {
        $("#BotsCerrarNoConformidad").show();
        MostrarDatoObser("Los campos resaltados son obligatorios.", true);
    }
}

let ultimaPeticionProceso = 0;
let procesoPendiente = '';

function CambioGestionProceso(mProceso = ''){
    const sel = document.getElementById('Proceso');
    const mCodGestion = document.getElementById('CodGestion').value;
    if(mProceso) procesoPendiente = mProceso;
    const seleccionado = procesoPendiente || sel.value;
    const id = ++ultimaPeticionProceso;

    sel.length = 1;
    if(!mCodGestion){ procesoPendiente = ''; $('#Proceso').trigger('change'); return; }

    $.ajax({
        type: "GET",
        url: "{{ url('/noconformidades/procesos') }}",
        dataType: "json",
        cache: false,
        data: { CodGestion: mCodGestion }
    }).done(function(r){
        if(id !== ultimaPeticionProceso) return;
        if(r.status !== 'success'){
            MostrarDatoObser(r.message || "Se presentó un error", true);
            return;
        }
        sel.length = 1;
        r.procesos.forEach(p => sel.add(new Option(p.nombre, p.codigo)));
        if(seleccionado && Array.from(sel.options).some(o => o.value === seleccionado)){
            sel.value = seleccionado;
        }
        procesoPendiente = '';
        $('#Proceso').trigger('change');
    });
}

function CambioTipo(){
    mCodTipo = document.getElementById('Tipo').value;
    mRequiereAC = document.getElementById('RequiereAC').value;
    if(mCodTipo == 'Corrección'){
        $("#DivRequiereAC").show();
        if(mRequiereAC == '-1'){
            $("#DivNumeroNCAccionCorrectiva").show();
        } else {
            $("#DivNumeroNCAccionCorrectiva").hide();
        }
    } else {
        $("#DivRequiereAC,#DivNumeroNCAccionCorrectiva").hide();
    }
}

function MostrarDivReportes(){
    $("#BotsReportes").show();
    $("#ModalReportes").modal('show');
}

function EnviarReportes(){
    MostrarDatoObser('Generando Reporte...', false);
    $("#BotsReportes").hide();
    mRetorno = true;
    
    mCampos = "#TipoReporte";
    $(mCampos).each(function(){
        if($(this).val()){
            $(this).removeClass("alert-danger is-invalid");
        } else {
            mRetorno = false;
            $(this).addClass("alert-danger is-invalid");
        }
    });
    
    if(mRetorno){
        (async() => {
            try {
                const url = "{{ url('/noconformidades/reportes') }}?TipoReporte=" + encodeURIComponent($("#TipoReporte").val()) +
                            "&ReportProceso=" + encodeURIComponent($("#ReportProceso").val());
                
                const response = await fetch(url);
                if(!response.ok) throw new Error("Fallo de red en el servidor");
                
                const raw_data = await response.json();
                
                if(raw_data[0] && raw_data[0].Codigo === 'Error'){
                    throw new Error(raw_data[0].Mensaje);
                }

                let mCamposConf = raw_data[0];
                raw_data.shift();
                const prez = raw_data;
                let rows = [];
                
                eval(mCamposConf['Campos']);
                const worksheet = XLSX.utils.json_to_sheet(rows);
                
                eval(mCamposConf['Formato']);
                const workbook = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(workbook, worksheet, "Datos");
                
                XLSX.writeFile(workbook, "Reporte_NoConformidades.xlsx", { compression: true });
                
                MostrarDatoObser("Reporte descargado exitosamente", false);
                $("#BotsReportes").show();
            } catch(e) {
                console.error("Error al generar Excel: ", e);
                $("#BotsReportes").show();
                MostrarDatoObser("Ocurrió un error generando el archivo. Revise la conexión.", true);
            }
        })();
    } else {
        $("#BotsReportes").show();
        MostrarDatoObser("El tipo de reporte es obligatorio.", true);
    }
}

function formatColumn(worksheet, col, fmt) {
    if(!worksheet['!ref']) return;
    const range = XLSX.utils.decode_range(worksheet['!ref']);
    for (let row = range.s.r + 1; row <= range.e.r; ++row) {
        const ref = XLSX.utils.encode_cell({ c: col, r: row });
        if (worksheet[ref] && worksheet[ref].t === 'n') {
            worksheet[ref].z = fmt;
        }
    }
}

function CambioTipoReporte(Obj){
    $("#DivFechasReporte").hide();
    if(['ReporteNoConformidades'].indexOf(Obj.value) >= 0){
        $("#DivFechasReporte").show();
    }
}

function ReversarNoConformidad(mNumeroNC){
    if(confirm('¿Está seguro que desea reversar esta no conformidad? Esto borrará la fecha de cierre y permitirá editarla nuevamente.')){
        MostrarDatoObser('Procesando');
        $.ajax({
            type: "POST",
            url: "{{ url('/noconformidades/reversar') }}",
            data: {
                TipoGrabar: 'ReversarNoConformidad',
                NumeroNC: mNumeroNC
            },
            dataType: 'json',
            cache: false,
        }).done(function(response){
            if(response.status === 'success'){
                MostrarDatoObser("No conformidad reversada exitosamente.", false);
                setTimeout(function(){ location.reload(); }, 1000);
            } else {
                MostrarDatoObser("Error al reversar: " + (response.message || ""), true);
            }
        });
    }
}
</script>
</head>
<body style="padding-top: 60px;" @if(request('DatoObser')) onLoad="MostrarDatoObser('{{ request('DatoObser') }}',false);" @endif>
<div class="myAlert-top alert alert-success" id="DatoObser" style="z-index:1000000; display:none;"></div>
<div class="container-fluid px-1">
<div class="container-fluid px-2 px-md-3 pb-4">
    <!-- TARJETA FIJA DE FILTROS ULTRA COMPACTA -->
    <div class="sticky-top filter-card" style="top: 5px;">
        
        <!-- Cabecera: Título y Controles Superiores -->
        <div class="d-flex justify-content-between align-items-center mb-1">
            
            <!-- Lado Izquierdo: Título y Versión -->
            <div class="d-flex align-items-center gap-2">
                <h6 class="mb-0 fw-bold" style="color: var(--primary-dark); font-size: 0.9rem;">No Conformidades & Mejoras</h6>
                <span class="badge-version" style="font-size: 0.6rem; padding: 0.15em 0.6em;">Versión 2</span>
                <span class="badge-code" style="font-size: 0.65rem;">Código PGGC-04-R-01</span>
            </div>
            
            <!-- Lado Derecho: Botones PC + Hamburguesa Móvil -->
            <div class="d-flex align-items-center gap-2">
                
                <!-- BOTONES VERSIÓN PC -->
                <div class="d-none d-xl-flex gap-1">
                    <button class="btn btn-dark-blue justify-content-center p-0" type="button" onClick="document.getElementById('FrmFiltrar').submit();" style="width: 28px;" title="Buscar">
                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/></svg>
                    </button>
                    <a onClick="MostrarDivEditarNoConformidad('Nuevo');" class="btn btn-gold justify-content-center p-0" style="width: 28px; cursor:pointer;" title="Nuevo Registro">
                        <svg width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/></svg>
                    </a>
                    <button class="btn btn-outline-gray justify-content-center p-0" type="button" onClick="MostrarDivReportes();" style="width: 28px;" title="Reportes">
                        <svg width="12" height="12" fill="currentColor" viewBox="0 0 16 16"><path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2zm15 2h-4v3h4V4zm0 4h-4v3h4V8zm0 4h-4v3h3a1 1 0 0 0 1-1v-2zm-5 3v-3H6v3h4zm-5 0v-3H1v2a1 1 0 0 0 1 1h3zm-4-4h4V8H1v3zm0-4h4V4H1v3zm5-3v3h4V4H6zm4 4H6v3h4V8z"/></svg>
                    </button>
                    <a href="{{ url('/') }}" class="btn btn-outline-gray justify-content-center p-0" style="width: 28px;" title="Inicio">
                        <svg width="13" height="13" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M2 13.5V7h1v6.5a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5V7h1v6.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 13.5zm11-11V6l-2-2V2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5z"/><path fill-rule="evenodd" d="M7.293 1.5a1 1 0 0 1 1.414 0l6.647 6.646a.5.5 0 0 1-.708.708L8 2.207 1.354 8.854a.5.5 0 1 1-.708-.708L7.293 1.5z"/></svg>
                    </a>
                </div>

                <!-- Botón Hamburguesa Móvil -->
                <button class="btn btn-light border d-xl-none p-1 shadow-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFiltros" style="width: 28px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </button>
            </div>
        </div>

        <!-- Contenedor Colapsable (Filtros y botones móviles) -->
        <div class="collapse d-xl-block" id="collapseFiltros">
            <form method="get" action="{{ url('/') }}" class="d-flex flex-wrap gap-2" name="FrmFiltrar" id="FrmFiltrar">
                <input name="Filtrar" type="hidden" id="Filtrar" value="1">
                
                <div style="flex: 1; min-width: 120px;">
                    <div class="filter-label">Gestión</div>
                    <select name="FiltroCodGestion" class="form-select form-select-sm" id="FiltroCodGestion">
                        <option value="">Gestión</option>
                        @foreach($gestiones as $RowGes)
                            <option value="{{ $RowGes->CodGestion }}" {{ request('FiltroCodGestion') == $RowGes->CodGestion ? 'selected' : '' }}>{{ $RowGes->NomGestion }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="flex: 1; min-width: 140px;">
                    <div class="filter-label">Proceso</div>
                    <select name="FiltroProceso" class="form-select form-select-sm" id="FiltroProceso">
                        <option value="">Cualquier proceso</option>
                        @foreach($procesos as $RowProc)
                            @php $codCompuesto = $RowProc->Colegio . $RowProc->CodProceso; @endphp
                            <option value="{{ $codCompuesto }}" {{ request('FiltroProceso') == $codCompuesto ? 'selected' : '' }}>{{ $RowProc->CodGestion . ' ' . $RowProc->NomProceso }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="flex: 1; min-width: 120px;">
                    <div class="filter-label">Tipo de acción</div>
                    <select name="FiltroTipo" class="form-select form-select-sm" id="FiltroTipo">
                        <option value="">Tipo Registro</option>
                        @foreach($mTipoRegistro as $Clave => $Valor)
                            <option value="{{ $Clave }}" {{ request('FiltroTipo') == $Clave ? 'selected' : '' }}>{{ $Clave }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="flex: 1; min-width: 110px;">
                    <div class="filter-label">Estado</div>
                    <select name="FiltroTipoAcciones" class="form-select form-select-sm" id="FiltroTipoAcciones">
                        <option value="">Todas</option>
                        <option value="1" {{ request('FiltroTipoAcciones') == 1 ? 'selected' : '' }}>Sin Acciones</option>
                        <option value="2" {{ request('FiltroTipoAcciones') == 2 ? 'selected' : '' }}>Con Acciones</option>
                        <option value="3" {{ request('FiltroTipoAcciones') == 3 ? 'selected' : '' }}>Por Cerrar</option>
                        <option value="4" {{ request('FiltroTipoAcciones') == 4 ? 'selected' : '' }}>Cerradas</option>
                    </select>
                </div>

                <div style="flex: 1; min-width: 130px;">
                    <div class="filter-label">Orden</div>
                    <select name="Ordencito" class="form-select form-select-sm" id="Ordencito">
                        <option value="2" {{ request('Ordencito') == 2 ? 'selected' : '' }}>Ordenar por Fecha</option>
                        <option value="1" {{ request('Ordencito') == 1 ? 'selected' : '' }}>Ordenar por Número</option>
                    </select>
                </div>

                <div style="width: 70px;">
                    <div class="filter-label">N° reg</div>
                    <input type="text" name="FiltroNumeroNC" id="FiltroNumeroNC" class="form-control form-control-sm text-center input-dashed" value="{{ request('FiltroNumeroNC') }}">
                </div>
                
                <!-- BOTONES VERSIÓN MÓVIL -->
                <div class="d-flex d-xl-none gap-1 w-100 mt-2 justify-content-end border-top pt-2">
                    <button class="btn btn-dark-blue justify-content-center p-0" type="button" onClick="document.getElementById('FrmFiltrar').submit();" style="width: 32px; height: 32px;" title="Buscar">
                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/></svg>
                    </button>
                    <a onClick="MostrarDivEditarNoConformidad('Nuevo');" class="btn btn-gold justify-content-center p-0" style="width: 32px; height: 32px; cursor:pointer;" title="Nuevo Registro">
                        <svg width="17" height="17" fill="currentColor" viewBox="0 0 16 16"><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/></svg>
                    </a>
                    <button class="btn btn-outline-gray justify-content-center p-0" type="button" onClick="MostrarDivReportes();" style="width: 32px; height: 32px;" title="Reportes">
                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 16 16"><path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2zm15 2h-4v3h4V4zm0 4h-4v3h4V8zm0 4h-4v3h3a1 1 0 0 0 1-1v-2zm-5 3v-3H6v3h4zm-5 0v-3H1v2a1 1 0 0 0 1 1h3zm-4-4h4V8H1v3zm0-4h4V4H1v3zm5-3v3h4V4H6zm4 4H6v3h4V8z"/></svg>
                    </button>
                    <a href="{{ url('/') }}" class="btn btn-outline-gray justify-content-center p-0" style="width: 32px; height: 32px;" title="Inicio">
                        <svg width="15" height="15" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M2 13.5V7h1v6.5a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5V7h1v6.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 13.5zm11-11V6l-2-2V2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5z"/><path fill-rule="evenodd" d="M7.293 1.5a1 1 0 0 1 1.414 0l6.647 6.646a.5.5 0 0 1-.708.708L8 2.207 1.354 8.854a.5.5 0 1 1-.708-.708L7.293 1.5z"/></svg>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- LISTADO DE NO CONFORMIDADES -->
    @if(request('Filtrar') == 1)
    <div class="row sidenav">
        <div class="tableFixHead border-0">
            <table class="table table-hover table-borderless align-middle mb-0">
                <thead>
                    <tr>
                        <th scope="col" width="10%">Fecha / ID</th>
                        <th scope="col" width="17%">Responsable apertura</th>
                        <th scope="col" width="12%">Proceso</th>
                        <th scope="col" width="41%">Descripción</th>
                        <th scope="col" width="10%">Detectada en</th>
                        <th scope="col" width="10%" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="TBodyNoConformidad" class="bg-white">
                @foreach($noConformidades as $Row)
                    <tr align="left">
                        <td>
                            {{ \App\Http\Controllers\NoConformidadController::darFechaHora($Row->FApertura, 1) }}
                            (<a style="cursor:pointer" onClick="MostrarDivConsultaNoConformidad({{ $Row->NumeroNC }});" title="Ver {{ $Row->NumeroNC }}">{{ $Row->NumeroNC }}</a>)
                        </td>
                        <td>{{ $Row->NomResponsable }}</td>
                        <td>{{ $Row->Proceso . ' (' . $Row->CodGestion . ')' }}</td>
                        <td>
                            <div class="desc-limitada" title="{{ $Row->Descripcion }}">
                                @if($Row->Borrada)
                                    <b style="background:red">BORRADA {{ $Row->Descripcion }}</b>
                                @else
                                    {{ $Row->Descripcion }}
                                @endif
                            </div>
                        </td>
                        <td>{{ $Row->DetectadaEn }}</td>
                        <td>
                            @if(!empty($Row->FCierre) && $Row->FCierre > '0000-00-00')
                                {{ \App\Http\Controllers\NoConformidadController::darFecha($Row->FCierre) }}
                                @if($PuedeReversar)
                                    <a style="cursor:pointer;" onClick="ReversarNoConformidad({{ $Row->NumeroNC }})" title="Reversar no conformidad">
                                        <svg width="20px" height="20px" viewBox="0 0 21 21" xmlns="http://www.w3.org/2000/svg" fill="#000000" stroke="#000000" stroke-width="1.932"><g stroke-width="0"></g><g stroke-linecap="round" stroke-linejoin="round"></g><g><g fill="none" fill-rule="evenodd" stroke="#db7500" stroke-linecap="round" stroke-linejoin="round" transform="matrix(0 1 1 0 2.5 2.5)"><path d="m3.98652376 1.07807068c-2.38377179 1.38514556-3.98652376 3.96636605-3.98652376 6.92192932 0 4.418278 3.581722 8 8 8s8-3.581722 8-8-8"></path><path d="m4 1v4h-4" transform="matrix(1 0 0 -1 0 6)"></path></g></g></svg>
                                    </a>
                                @endif
                            @else
                                <a style="cursor:pointer" onClick="MostrarDivEditarNoConformidad({{ $Row->NumeroNC }});" title="Ver {{ $Row->NumeroNC }}">
                                    <img hspace="2" width="16" height="16" src="{{ asset('imagenes/editar.png') }}" onerror="this.outerHTML='✏️'" border="0"/>
                                </a>
                                <a style="cursor:pointer" onClick="MostrarDivSeguimientos({{ $Row->NumeroNC }});" title="Editar Seguimientos ID {{ $Row->NumeroNC }}">
                                    <img hspace="2" width="16" height="16" src="{{ asset('imagenes/nuevoregistro.png') }}" onerror="this.outerHTML='📋'" border="0"/>
                                </a>
                                @if($PuedeCerrar)
                                    <a style="cursor:pointer" onClick="MostrarDivCerrarNoConformidad({{ $Row->NumeroNC }})" title="Cerrar registro">
                                        <img hspace="2" width="16" height="16" src="{{ asset('imagenes/terminar.png') }}" onerror="this.outerHTML='🔒'" border="0"/>
                                    </a>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
</div>

<!-- VENTANA MODAL CONSULTA DE NO CONFORMIDAD (DETALLE) -->
<div class="modal fade" id="ModalConsultaNoConformidad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">DETALLE DE NO CONFORMIDAD</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body row g-2" id="ModalConsultaNoConformidadBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- VENTANA MODAL EDICIÓN NO CONFORMIDADES -->
<div class="modal fade" id="ModalEditarNoConformidad" tabindex="-1" aria-labelledby="ModalEditarNoConformidadTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title fw-bold text-primary d-flex align-items-center" id="ModalEditarNoConformidadTitle">
                    <svg width="24" height="24" class="me-2" fill="currentColor" viewBox="0 0 16 16"><path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h13zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13z"/><path d="M5 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 5 8zm0-2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5zm0 5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5zm-1-5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0zM4 8a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0zm0 2.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0z"/></svg>
                    EDITAR/CREAR NO CONFORMIDAD
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4" id="DivBodyNoConformidad">
              <form method="post" enctype="multipart/form-data" name="FrmNoConformidad" id="FrmNoConformidad" autocomplete="off">
                <input name="TipoGrabar" type="hidden" id="TipoGrabar">
                <input name="TipoModificar" type="hidden" id="TipoModificar">
                <input name="NumeroNC" type="hidden" id="NumeroNC">
                
                <div class="row g-4">
                    <!-- Columna 1: Datos Generales -->
                    <div class="col-lg-4">
                        <div class="bg-light border rounded p-3 h-100">
                            <h6 class="text-secondary border-bottom pb-2 mb-3">Datos Generales</h6>
                            
                            <div class="mb-3">
                                <label for="Responsable" class="form-label text-muted small mb-1 fw-bold">Responsable de la apertura</label>
                                <input type="text" name="Responsable" id="Responsable" class="form-control form-control-sm w-100" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label for="Tipo" class="form-label text-muted small mb-1 fw-bold">Tipo</label>
                                <select name="Tipo" class="form-select form-select-sm w-100" id="Tipo" onChange="CambioTipo()">
                                    <option value="">...</option>
                                    @foreach($mTipoRegistro as $Clave => $Valor)
                                        <option value="{{ $Clave }}">{{ $Clave . ' - ' . $Valor }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="CodGestion" class="form-label text-muted small mb-1 fw-bold">Gestión</label>
                                <select name="CodGestion" class="form-select form-select-sm w-100" id="CodGestion" onChange="CambioGestionProceso();">
                                    <option value=""></option>
                                    @foreach($gestiones as $RowGes)
                                        <option value="{{ $RowGes->CodGestion }}">{{ $RowGes->NomGestion }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="Proceso" class="form-label text-muted small mb-1 fw-bold">Proceso en donde se detecta</label>
                                <select name="Proceso" class="form-select form-select-sm w-100" id="Proceso">
                                    <option value="" selected>...</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="FApertura" class="form-label text-muted small mb-1 fw-bold">Fecha de apertura</label>
                                <input type="text" name="FApertura" id="FApertura" class="form-control form-control-sm w-100" onChange="ValidarFecha(this)" onBlur="ValidarFecha(this)">
                            </div>
                            
                            <div class="mb-3">
                                <label for="DetectadaEn" class="form-label text-muted small mb-1 fw-bold">Detectada en</label>
                                <select name="DetectadaEn" class="form-select form-select-sm w-100" id="DetectadaEn" onChange="ValidarDetectadaEn(this.value);">
                                      <option value="">...</option>
                                      <option value="Auditoría Interna">Auditoría Interna</option>
                                      <option value="Auditoría Externa">Auditoría Externa</option>
                                      <option value="Control de proceso">Control de proceso</option>
                                      <option value="Encuesta de satisfacción">Encuesta de satisfacción</option>
                                      <option value="Quejas">Quejas</option>
                                      <option value="Revisión por la dirección">Revisión por la dirección</option>
                                      <option value="Otra">Otra</option>
                                </select>
                            </div>
                            
                            <div class="mb-3" id="DivDetectadaEnOtra">
                                <label class="form-label text-muted small mb-1 fw-bold">¿Cuál?</label>
                                <input name="DetectadaEnOtra" class="form-control form-control-sm w-100" id="DetectadaEnOtra" maxlength="30">
                            </div>
                        </div>
                    </div>

                    <!-- Columna 2: Detalles de la Acción -->
                    <div class="col-lg-4">
                        <div class="border rounded p-3 h-100">
                            <h6 class="text-secondary border-bottom pb-2 mb-3">Descripción de la Acción</h6>
                            <div class="mb-3">
                                <label for="Descripcion" class="form-label text-muted small mb-1 fw-bold">Descripción</label>
                                <div class="alert alert-warning py-1 px-2 mb-2" style="font-size: 0.75rem;">Realice una descripción de la Acción que se materializó o de la acción que desea potencializarse.</div>
                                <textarea name="Descripcion" id="Descripcion" class="form-control form-control-sm" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="Correccion" class="form-label text-muted small mb-1 fw-bold">Relación de actividades:</label>
                                <textarea name="Correccion" id="Correccion" class="form-control form-control-sm" placeholder="Describa las actividades..." rows="3"></textarea>
                            </div>
                            <div class="mb-3" id="DivRequiereAC">
                                <label for="RequiereAC" class="form-label text-muted small mb-1 fw-bold">¿Garantiza que la falla no se presente de nuevo?</label>
                                <select name="RequiereAC" class="form-select form-select-sm" id="RequiereAC" onChange="CambioTipo();">
                                      <option value="">...</option>
                                      <option value="1">Si</option>
                                      <option value="-1">No</option>
                                </select>
                            </div>
                            <div class="mb-3" id="DivNumeroNCAccionCorrectiva">
                                <label for="NumeroNCAccionCorrectiva" class="form-label text-muted small mb-1 fw-bold">ID de la acción correctiva vinculada</label>
                                <input name="NumeroNCAccionCorrectiva" class="form-control form-control-sm" id="NumeroNCAccionCorrectiva" maxlength="11" onBlur="ValidarNumeros(this);">
                            </div>
                        </div>
                    </div>

                    <!-- Columna 3: Plan y Archivos -->
                    <div class="col-lg-4">
                        <div class="bg-light border rounded p-3 h-100">
                            <h6 class="text-secondary border-bottom pb-2 mb-3">Plan de Acción y Adjuntos</h6>
                            <div class="mb-3">
                                <label for="Causas" class="form-label text-muted small mb-1 fw-bold">Causas o Ventajas</label>
                                <textarea name="Causas" id="Causas" class="form-control form-control-sm" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="Accion" class="form-label text-muted small mb-1 fw-bold">Objetivo principal del plan de Acción</label>
                                <textarea name="Accion" id="Accion" class="form-control form-control-sm" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="FRevision" class="form-label text-muted small mb-1 fw-bold">Fecha acordada para la revisión</label>
                                <input type="text" name="FRevision" id="FRevision" class="form-control form-control-sm" onChange="ValidarFecha(this)" onBlur="ValidarFecha(this)">
                            </div>
                            
                            <div class="mb-3 mt-4">
                                <label for="FileNoConformidad" class="form-label text-muted small mb-1 fw-bold">Adjuntar Archivo PDF (Max 4MB)</label>
                                <input class="form-control form-control-sm" name="FileNoConformidad" id="FileNoConformidad" type="file" accept="application/pdf">
                            </div>
                        </div>
                    </div>
                </div>
              </form>
            </div>
            <div class="modal-footer bg-light" id="BotsNoConformidad">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary px-4" onClick="GrabarNoConformidad();">Guardar Registro</button>
            </div>
        </div>
    </div>
</div>

<!-- VENTANA MODAL SEGUIMIENTOS -->
<div class="modal fade" id="ModalSeguimientos" tabindex="-1" role="dialog" aria-labelledby="ModalSeguimientosTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="col-sm-11">
                    <h4 class="modal-title" id="ModalSeguimientosTitle">EDITAR SEGUIMIENTOS CONFORMIDAD</h4>
                </div>
                <div class="col-sm-1 text-end">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body row" id="DivBodySeguimientos">
              <form method="post" enctype="multipart/form-data" class="form_validar" name="FrmSeguimientos" id="FrmSeguimientos" autocomplete="off">
                <input name="TipoGrabar" type="hidden" id="TipoGrabar">
                <input name="TipoModificar" type="hidden" id="TipoModificar">
                <input name="NumeroNCSeguimientos" type="hidden" id="NumeroNCSeguimientos">
                <div class="col-sm-12" id="DivSeguimientos"></div>
              </form>
            </div>
            <div class="modal-footer" id="BotsSeguimientos">
                <input type="button" class="btn btn-primary" onClick="AgregarSeguimiento();" value="Otro Seguimiento">
                <input type="button" class="btn btn-primary" onClick="GrabarSeguimientos();" value="Aceptar">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- VENTANA MODAL CERRAR NO CONFORMIDADES -->
<div class="modal fade" id="ModalCerrarNoConformidad" tabindex="-1" role="dialog" aria-labelledby="ModalCerrarNoConformidadTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title col-sm-11 fw-bold" id="ModalCerrarNoConformidadTitle">DATOS DEL CIERRE DEL REGISTRO</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="DivBodyCerrarNoConformidad">
              <form method="post" enctype="multipart/form-data" class="form_validar row g-3" name="FrmCerrarNoConformidad" id="FrmCerrarNoConformidad" autocomplete="off">
                <input name="TipoGrabar" type="hidden" id="TipoGrabar">
                <input name="TipoModificar" type="hidden" id="TipoModificar">
                <input name="NumeroNCCerrar" type="hidden" id="NumeroNCCerrar">
                <div class="col-sm-6 form-group">
                    <label for="ResultadoRevision" class="label-xsmall fw-bold">Resultados de la revisión de la eficacia</label>
                    <textarea name="ResultadoRevision" id="ResultadoRevision" class="form-control small" rows="3"></textarea>
                </div>
                <div class="col-sm-6 form-group">
                    <label for="MotivoCierre" class="label-xsmall fw-bold">Motivos del Cierre</label>
                    <textarea name="MotivoCierre" id="MotivoCierre" class="form-control small" rows="3"></textarea>
                </div>
                <div class="col-sm-6 form-group">
                    <label for="NuevaAccion" class="label-xsmall fw-bold">Nueva Acción?</label>
                    <textarea name="NuevaAccion" id="NuevaAccion" class="form-control small" rows="3"></textarea>
                </div>
                <div class="col-sm-2 form-group">
                    <label for="FCierre" class="label-xsmall fw-bold">Fecha del Cierre</label>
                    <input type="text" name="FCierre" id="FCierre" class="form-control" onChange="ValidarFecha(this)" onBlur="ValidarFecha(this)">
                </div>
                <div class="col-sm-4 form-group">
                    <label for="RCierre" class="label-xsmall fw-bold">Responsable del Cierre</label>
                    <input type="text" name="RCierre" id="RCierre" class="form-control">
                </div>
              </form>
            </div>
            <div class="modal-footer" id="BotsCerrarNoConformidad">
                <input type="button" class="btn btn-primary" onClick="GrabarCerrarNoConformidad();" value="Aceptar">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- VENTANA MODAL REPORTES -->
<div class="modal fade" id="ModalReportes" tabindex="-1" role="dialog" aria-labelledby="ModalReportesTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ModalReportesTitle"><b>Consulta Reportes</b></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form method="post" enctype="multipart/form-data" class="form_validar row g-2" name="FrmReportes" id="FrmReportes" autocomplete="off" target="_blank">
                <input name="TipoGrabar" type="hidden" id="TipoGrabar">
                <input name="TipoModificar" type="hidden" id="TipoModificar">
                <div class="col-sm-5 form-group">
                    <label class="label-xsmall fw-bold">Tipo Reporte</label>
                    <select name="TipoReporte" class="form-select" id="TipoReporte" onChange="CambioTipoReporte(this);">
                        <option value="">...</option>
                        <option value="ReporteNoConformidades">Todos los registros</option>
                    </select>
                </div>
                <div class="col-sm-7 form-group" id="DivFechasReporte">
                    <label class="label-xsmall fw-bold">Proceso</label>
                    <select name="ReportProceso" id="ReportProceso" class="form-select">
                        <option value="">Proceso(Todos)</option>
                        @foreach($procesos as $RowProc)
                            @php $codCompuesto = $RowProc->Colegio . $RowProc->CodProceso; @endphp
                            <option value="{{ $codCompuesto }}" {{ request('FiltroProceso') == $codCompuesto ? 'selected' : '' }}>{{ $RowProc->CodGestion . ' ' . $RowProc->NomProceso }}</option>
                        @endforeach
                    </select>
                </div>
              </form>
            </div>
            <div class="modal-footer" id="BotsReportes">
                <input type="button" class="btn btn-primary" onClick="EnviarReportes();" value="Aceptar">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
</body>
</html>