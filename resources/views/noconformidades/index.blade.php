<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
    <!-- TOKEN CSRF OBLIGATORIO PARA LARAVEL -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MANEJO DE NO CONFORMIDADES Y MEJORAS</title>
    
    <!-- Tus librerías originales -->
    <link rel="stylesheet" href="{{ asset('librerias/jquery/jquery-ui.css') }}">
    <script src="{{ asset('librerias/jquery/jquery-1.10.2.js') }}"></script>
    <script src="{{ asset('librerias/jquery/jquery-ui.js') }}"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <style>
        /* Pega aquí todo tu bloque de estilos CSS original exactamente igual */
        :root { --primary-dark: #1A2B4C; --accent-gold: #EAA315; --bg-light: #F4F7F6; }
        body { background-color: var(--bg-light); padding-top: 20px !important; font-family: system-ui; }
        /* ... (resto de tu CSS) ... */
    </style>

    <script type="text/javascript">
        // 1. CONFIGURACIÓN GLOBAL DE LARAVEL PARA AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // 2. INICIALIZACIÓN (Igual que el tuyo)
        $(function(){
            $('#DivDetectadaEnOtra,#DivRequiereAC,#DivNumeroNCAccionCorrectiva').hide();
            // ... (Configuración de datepicker y select2 igual que tu original) ...
        });

        function MostrarDatoObser(msg, esError = false){
            let mDiv = $("#DatoObser");
            mDiv.hide().html((esError ? "⚠️ " : "✅ ") + msg);
            esError ? mDiv.removeClass("alert-success").addClass("alert-danger fw-bold") 
                    : mDiv.removeClass("alert-danger fw-bold").addClass("alert-success");
            mDiv.show();
            setTimeout(function(){ mDiv.hide(); }, 3500); 
        }

        // 3. RUTAS AJAX ACTUALIZADAS A LARAVEL
        function MostrarDivConsultaNoConformidad(mNumeroNC){
            // Usamos la ruta limpia de Laravel
            $("#ModalConsultaNoConformidadBody").load("{{ url('noconformidades/show') }}/" + mNumeroNC, function() {
                $("#ModalConsultaNoConformidad").modal('show');
            });
        }

        function MostrarDivEditarNoConformidad(mNumeroNC){
            document.FrmNoConformidad.reset();
            document.getElementById('NumeroNC').value = mNumeroNC;
            $("#FrmNoConformidad").find("input:text,select,textarea").removeClass("alert-danger is-invalid");
            
            if(mNumeroNC == 'Nuevo'){
                // ... (Tu lógica de Nuevo registro igual) ...
                $("#ModalEditarNoConformidad").modal('show');
            } else {
                $.ajax({
                    type: "GET",
                    url: "{{ url('noconformidades/edit') }}/" + mNumeroNC, // Ruta Laravel
                    dataType: "json",
                    cache: false,
                }).done(function(data){
                    if(data.status === "Error") return MostrarDatoObser("Se presentó un error", true);
                    
                    // Llenar campos (Igual que tu original)
                    document.getElementById('CodGestion').value = data.CodGestion;
                    CambioGestionProceso(data.Proceso);
                    // ... (resto de asignaciones) ...
                    $("#ModalEditarNoConformidad").modal('show');
                });
            }
        }

        function GrabarNoConformidad(){
            MostrarDatoObser('Procesando...', false);
            // ... (Tu validación de campos obligatorios igual) ...
            
            if(mRetorno){
                var f = new FormData(document.getElementById('FrmNoConformidad'));
                $.ajax({
                    url: "{{ url('noconformidades/store') }}", // Ruta POST Laravel
                    type: 'POST',
                    data: f,
                    dataType: "json",
                    processData: false,
                    contentType: false
                }).done(function(response){
                    if(response.status === 'success'){
                        MostrarDatoObser("Guardado correctamente.", false);
                        $("#FiltroNumeroNC").val(response.id);
                        setTimeout(() => document.FrmFiltrar.submit(), 800);
                    } else {
                        MostrarDatoObser(response.message || "Error", true);
                    }
                });
            }
        }

        function CambioGestionProceso(mProceso=''){
            const sel = document.getElementById('Proceso');
            const mCodGestion = document.getElementById('CodGestion').value;
            // ...
            if(!mCodGestion){ $('#Proceso').trigger('change'); return; }

            $.ajax({
                type: "GET", 
                url: "{{ url('noconformidades/procesos') }}/" + mCodGestion, // Ruta GET Laravel
                dataType: "json"
            }).done(function(r){
                if(r.status !== 'success') return MostrarDatoObser(r.message, true);
                sel.length = 1;
                r.procesos.forEach(p => sel.add(new Option(p.nombre, p.codigo)));
                if(mProceso) sel.value = mProceso;
                $('#Proceso').trigger('change');
            });
        }
        
        // ... (Puedes pegar el resto de tus funciones JS como Reversar, Reportes, Seguimientos 
        // cambiando los llamados a noconformidades.php por {{ url('noconformidades/...') }})
    </script>
</head>
<body style="padding-top: 60px;">
    <div class="myAlert-top alert alert-success" id="DatoObser" style="z-index:1000000; display:none;"></div>
    
    <div class="container-fluid px-1">
        <!-- TARJETA FIJA DE FILTROS -->
        <div class="sticky-top filter-card" style="top: 5px;">
            <!-- ... (Tu cabecera HTML igual) ... -->
            
            <!-- SECCIÓN DE FILTROS VISIBLE -->
        <div class="card shadow-sm mb-3">
            <div class="card-body bg-light py-2">
                <form method="get" action="{{ url('/') }}" name="FrmFiltrar" id="FrmFiltrar" class="row g-2 align-items-end">
                    <input name="Filtrar" type="hidden" id="Filtrar" value="1">
                    
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-secondary mb-1">Gestión:</label>
                        <select name="FiltroCodGestion" class="form-select form-select-sm" id="FiltroCodGestion">
                            <option value="">-- Seleccione Gestión --</option>
                            @foreach($gestiones as $gestion)
                                <option value="{{ $gestion->CodGestion }}" {{ request('FiltroCodGestion') == $gestion->CodGestion ? 'selected' : '' }}>
                                    {{ $gestion->NomGestion }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-8 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-4">
                            🔍 Filtrar / Buscar
                        </button>
                        <a href="{{ url('noconformidades') }}" class="btn btn-outline-secondary btn-sm">
                            Limpiar
                        </a>
                        <button type="button" class="btn btn-success btn-sm ms-auto" onClick="MostrarDivEditarNoConformidad('Nuevo');">
                            ➕ Nueva No Conformidad
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- TABLA DE RESULTADOS -->
        @if(request('Filtrar') == 1)
        <div class="row sidenav">
            <div class="tableFixHead border-0">
                <table class="table table-hover table-borderless align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col" width="10%">Fecha / ID</th>
                            <th scope="col" width="17%">Responsable apertura</th>
                            <th scope="col" width="41%">Descripción</th>
                            <th scope="col" width="10%" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="TBodyNoConformidad" class="bg-white">
                        <!-- MAGIA BLADE: Iterar No Conformidades -->
                        @foreach($noConformidades as $nc)
                        <tr align="left">
                            <td>{{ \Carbon\Carbon::parse($nc->FApertura)->format('d/m/Y H:i') }}
                                (<a style="cursor:pointer" onClick="MostrarDivConsultaNoConformidad({{ $nc->NumeroNC }});">{{ $nc->NumeroNC }}</a>)
                            </td>
                            <td>{{ $nc->NomResponsable }}</td>
                            <td>
                                <div class="desc-limitada">
                                    @if($nc->Borrada)
                                        <b style="background:red">BORRADA {{ $nc->Descripcion }}</b>
                                    @else
                                        {{ $nc->Descripcion }}
                                    @endif
                                </div>
                            </td>
                            <td>
                                <!-- Lógica de botones igual a tu PHP pero con Blade -->
                                @if($nc->FCierre > 0)
                                    {{ \Carbon\Carbon::parse($nc->FCierre)->format('d/m/Y') }}
                                @else
                                    <a style="cursor:pointer" onClick="MostrarDivEditarNoConformidad({{ $nc->NumeroNC }});"><img hspace='2' width='16' height='16' src="{{ asset('imagenes/editar.png') }}"/></a>
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

    <!-- Pega aquí tus 4 Modales (Crear, Seguimientos, Cierre y Reportes) exactamente igual,
         solo asegurándote de cambiar las variables PHP por-->
         
</body>
</html>