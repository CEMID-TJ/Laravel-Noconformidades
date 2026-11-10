<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, viewport-fit=cover">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="theme-color" content="#2563eb">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MANEJO DE NO CONFORMIDADES Y MEJORAS</title>

    <!-- JQuery & JQuery UI -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

    <!-- Bootstrap 5.3.3 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SheetJS (Excel) -->
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

    <!-- Funciones locales + Respaldo -->
    <script src="{{ asset('funciones.js') }}"></script>
    <script>
        if (typeof ValidarFecha !== 'function') { function ValidarFecha(obj) { return true; } }
        if (typeof ValidarNumeros !== 'function') { function ValidarNumeros(obj) { obj.value = obj.value.replace(/[^0-9]/g, ''); } }
        function SoloDigitos(e) { e.value = e.value.replace(/[^0-9]/g, ''); }
    </script>

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/es.js"></script>

    <style>
        /* ===== Variables y base ===== */
        :root {
            --bg-body: #e9eef4;
            --bg-card: #ffffff;
            --border: #b7c3d2;
            --border-soft: #cbd5e1;
            --border-strong: #94a3b8;
            --text: #0b1322;
            --text-muted: #334155;
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --primary-soft: #eff6ff;
            --success: #16a34a;
            --success-hover: #15803d;
            --success-bg: #dcfce7;
            --success-border: #86efac;
            --danger: #dc2626;
            --danger-hover: #b91c1c;
            --danger-bg: #fee2e2;
            --danger-border: #fca5a5;
            --info: #0284c7;
            --info-bg: #e0f2fe;
            --info-border: #7dd3fc;
            --purple: #7c3aed;
            --purple-bg: #f5f3ff;
            --shadow-sm: 0 1px 3px rgba(11, 19, 34, .08);
            --shadow: 0 4px 14px rgba(11, 19, 34, .10);
            --shadow-lg: 0 20px 60px rgba(11, 19, 34, .25);
            --navbar-height: 112px;
        }

        html { background: var(--bg-body); overflow-y: scroll !important; scrollbar-gutter: stable; }
        body {
            padding-right: 0 !important;
            background: var(--bg-body);
            color: var(--text);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Inter', Roboto, sans-serif;
            font-size: 14px;
            line-height: 1.5;
        }
        body.modal-open { padding-right: 0 !important; }
        .container-main { width: 96%; max-width: 1600px; margin: 0 auto; background: transparent; padding: 14px 16px 0 16px; }

        /* ===== Navbar Fija en 2 Niveles (Idéntica a la imagen) ===== */
        .navbar-default {
            background: #fff !important;
            border: none !important;
            box-shadow: var(--shadow-sm);
            padding: 8px 24px 12px 24px;
            min-height: 64px;
            z-index: 1030;
        }
        .navbar-top-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            margin-bottom: 6px;
        }
        .navbar-brand-custom {
            color: var(--text) !important;
            font-weight: 700;
            font-size: 15px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 4px 0;
            line-height: 1.2;
            text-decoration: none;
        }
        .navbar-brand-custom .brand-icon {
            color: var(--primary);
            font-size: 25px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            flex: 0 0 28px;
        }
        .navbar-brand-custom .label-xsmall {
            display: block;
            font-size: 10px;
            color: var(--text-muted);
            font-weight: 600;
            margin-top: 3px;
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        /* Caja de Botones Derecha (Lupa, +, Excel, Casa) */
        .actions-box {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 3px;
            background: #eef2f7;
            border: 1.5px solid var(--border);
            border-radius: 9px;
            box-shadow: inset 0 1px 2px rgba(11, 19, 34, .04);
            height: 40px;
            width: fit-content;
        }
        .btn-nav {
            width: 32px;
            height: 32px;
            padding: 0 !important;
            border-radius: 6px !important;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            background: #fff !important;
            border: 1px solid var(--border) !important;
            color: var(--text-muted) !important;
            font-size: 14px;
            transition: all .15s;
            margin: 0;
            line-height: 1;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-nav:hover {
            background: var(--primary-soft) !important;
            border-color: var(--primary) !important;
            color: var(--primary) !important;
            transform: translateY(-1px);
        }
        .btn-nav-primary {
            background: var(--primary) !important;
            border-color: var(--primary) !important;
            color: #fff !important;
        }
        .btn-nav-primary:hover {
            background: var(--primary-hover) !important;
            border-color: var(--primary-hover) !important;
            color: #fff !important;
        }

        /* ===== Select2 Personalizado (Estilo píldora limpia como en la imagen) ===== */
        .select2-container .select2-selection--single {
            height: 38px !important;
            border: 1.5px solid var(--border) !important;
            border-radius: 8px !important;
            background-color: #f7f9fc !important;
            display: flex !important;
            align-items: center;
        }
        .modal .select2-container .select2-selection--single {
            background-color: #fff !important;
            height: 40px !important;
        }
        .select2-container .select2-selection--single .select2-selection__rendered {
            color: var(--text) !important;
            font-size: 13px !important;
            padding-left: 12px !important;
            padding-right: 26px !important;
            line-height: 36px !important;
        }
        .select2-container .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
            right: 8px !important;
        }
        .select2-container--open .select2-selection--single {
            border-color: var(--primary) !important;
            background-color: #fff !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .18) !important;
        }
        .select2-dropdown {
            border: 1.5px solid var(--border) !important;
            border-radius: 8px !important;
            box-shadow: var(--shadow) !important;
            font-size: 13px !important;
            z-index: 9999999 !important;
        }
        .select2-results__option--highlighted[aria-selected] {
            background-color: var(--primary) !important;
            color: #fff !important;
        }
        .select2-search--dropdown .select2-search__field {
            border: 1.5px solid var(--border) !important;
            border-radius: 6px !important;
            padding: 6px 10px !important;
            font-size: 13px !important;
            outline: none;
        }

        /* ===== Formularios ===== */
        .form-control, .form-select {
            border: 1.5px solid var(--border) !important;
            border-radius: 8px !important;
            box-shadow: none !important;
            height: 40px;
            padding: 8px 12px;
            font-size: 13px;
            background: #fff;
            color: var(--text);
            transition: all .15s;
        }
        .navbar-default .form-control {
            height: 38px !important;
            background: #f7f9fc;
            padding: 6px 12px;
        }
        .form-control:focus, .form-select:focus {
            background: #fff;
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .18) !important;
            outline: none;
        }
        input.form-control[readonly] { background: #f1f5f9 !important; cursor: pointer; }
        textarea.form-control { height: auto !important; min-height: 80px; resize: vertical; }

        label, .label-xsmall {
            font-weight: 700;
            font-size: 11.5px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .4px;
            margin-bottom: 6px !important;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .form-control.alert-danger, select.form-control.alert-danger,
        textarea.form-control.alert-danger, input.form-control.alert-danger {
            border-color: var(--danger) !important;
            background-color: #fef2f2 !important;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, .18) !important;
            animation: shakeError .35s ease;
        }
        @keyframes shakeError {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-4px); }
            50% { transform: translateX(4px); }
            75% { transform: translateX(-3px); }
        }

        /* ===== Botones ===== */
        .btn {
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            padding: 8px 16px;
            border: 1px solid transparent;
            transition: all .15s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-primary { background: var(--primary); border-color: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-hover); border-color: var(--primary-hover); color: #fff; }
        .btn-success { background: var(--success); border-color: var(--success); color: #fff; }
        .btn-success:hover { background: var(--success-hover); border-color: var(--success-hover); color: #fff; }
        .btn-danger { background: var(--danger); border-color: var(--danger); color: #fff; }
        .btn-danger:hover { background: var(--danger-hover); border-color: var(--danger-hover); color: #fff; }
        .btn-secondary { background: #f1f5f9; border-color: var(--border); color: var(--text); }
        .btn-secondary:hover { background: #e2e8f0; border-color: var(--border-strong); color: var(--text); }

        /* ===== Paneles Internos ===== */
        .panel {
            border: 1.5px solid var(--border) !important;
            border-radius: 12px !important;
            box-shadow: var(--shadow-sm) !important;
            margin-bottom: 14px !important;
            background: #fff !important;
            padding: 12px 16px;
        }
        .panel-primary { border-color: #93c5fd !important; background: #f5f9ff !important; }
        .panel-primary h5 {
            margin: 4px 0 12px;
            color: var(--primary);
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        /* ===== Tabla con Colores y Bordes Idénticos a la Captura ===== */
        .tableFixHead {
            position: relative;
            background: #ffffff;
            border: 1.5px solid #b7c3d2;
            border-radius: 14px;
            box-shadow: 0 4px 14px rgba(11, 19, 34, .10);
            padding: 0;
            margin: 0 0 15px 0;
            overflow: visible !important;
            display: block;
        }
        .tableFixHead table {
            width: 100%;
            margin: 0;
            border-collapse: separate !important;
            border-spacing: 0 !important;
            --bs-table-striped-bg: transparent !important;
            --bs-table-hover-bg: transparent !important;
        }
        
        /* Encabezado gris azulado (#eef2f7) con texto oscuro (#1e293b) */
        .tableFixHead thead th {
            position: sticky;
            top: calc(var(--navbar-height, 112px) - 1px);
            z-index: 1020;
            background-color: #eef2f7 !important;
            color: #1e293b !important;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .5px;
            font-weight: 700;
            text-align: center !important;
            vertical-align: middle !important;
            border-top: none !important;
            border-left: none !important;
            border-bottom: 1.5px solid #b7c3d2 !important;
            border-right: 1px solid #cbd5e1 !important;
            padding: 14px 16px !important;
        }
        .tableFixHead thead th:first-child { border-top-left-radius: 12.5px; }
        .tableFixHead thead th:last-child { border-top-right-radius: 12.5px; border-right: none !important; }

        /* Celdas del cuerpo y líneas divisorias (#cbd5e1) */
        .tableFixHead tbody td {
            text-align: left;
            color: #0b1322 !important;
            border-top: none !important;
            border-left: none !important;
            border-bottom: 1px solid #cbd5e1 !important;
            border-right: 1px solid #cbd5e1 !important;
            vertical-align: middle;
            line-height: 1.4;
            padding: 12px 16px !important;
            font-size: 13px;
            transition: background-color .12s ease;
        }
        .tableFixHead tbody td:last-child { border-right: none !important; white-space: nowrap; }

        /* Alternancia de colores en filas (Blanco y Gris muy tenue #fafbfc) */
        .tableFixHead tbody tr:nth-of-type(even) td {
            background-color: #ffffff !important;
        }
        .tableFixHead tbody tr:nth-of-type(odd) td {
            background-color: #fafbfc !important;
        }

        /* Efecto Hover / Primera fila resaltada en celeste suave (#f0f7ff) */
        .tableFixHead tbody tr:hover td {
            background-color: #f0f7ff !important;
        }

        /* Bordes inferiores redondeados en la última fila */
        .tableFixHead tbody tr:last-child td { border-bottom: none !important; }
        .tableFixHead tbody tr:last-child td:first-child { border-bottom-left-radius: 12.5px; }
        .tableFixHead tbody tr:last-child td:last-child { border-bottom-right-radius: 12.5px; }

        /* Enlace del ID en azul brillante (#2563eb) */
        .link-modern {
            color: #2563eb !important;
            text-decoration: none;
            font-weight: 600;
        }
        .link-modern:hover { text-decoration: underline; }

        /* Botones de Acción (Azul pastel y Morado pastel como en la imagen) */
        .acciones-cell {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            flex-wrap: nowrap;
        }
        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
            transition: all .15s;
            border: 1px solid transparent;
            text-decoration: none !important;
        }
        .action-edit {
            color: #2563eb !important;
            background-color: #eff6ff !important;
        }
        .action-edit:hover {
            background-color: #dbeafe !important;
            border-color: #bfdbfe !important;
            color: #1d4ed8 !important;
        }
        .action-list {
            color: #7c3aed !important;
            background-color: #f5f3ff !important;
        }
        .action-list:hover {
            background-color: #ede9fe !important;
            border-color: #ddd6fe !important;
            color: #6d28d9 !important;
        }
        .action-success {
            color: #16a34a !important;
            background-color: #dcfce7 !important;
        }
        .action-success:hover {
            background-color: #bbf7d0 !important;
            border-color: #86efac !important;
            color: #15803d !important;
        }
        .action-danger {
            color: #dc2626 !important;
            background-color: #fee2e2 !important;
        }
        .action-danger:hover {
            background-color: #fecaca !important;
            border-color: #fca5a5 !important;
            color: #b91c1c !important;
        }

        /* ===== Paginación Idéntica a la Imagen ===== */
        .nc-pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            padding: 12px 18px;
            background: #fff;
            border: 1.5px solid var(--border);
            border-radius: 12px;
            box-shadow: var(--shadow-sm);
            margin: 0 0 24px 0;
        }
        .nc-pagination-info { font-size: 12.5px; color: var(--text-muted); font-weight: 600; }
        .nc-pagination-controls { display: flex; align-items: center; gap: 5px; flex-wrap: wrap; }
        .page-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 34px;
            height: 34px;
            padding: 0 11px;
            border-radius: 8px;
            border: 1.5px solid var(--border);
            background: #fff;
            color: var(--text);
            font-size: 12.5px;
            font-weight: 600;
            text-decoration: none;
            transition: all .15s;
            line-height: 1;
        }
        .page-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-soft);
            text-decoration: none;
        }
        .page-btn.active {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }
        .page-btn.disabled {
            opacity: .45;
            pointer-events: none;
        }
        .page-ellipsis { padding: 0 4px; color: var(--text-muted); font-weight: 700; }

        /* ===== Alertas ===== */
        .myAlert-top {
            position: fixed;
            top: 90px;
            left: 50%;
            transform: translateX(-50%);
            width: auto;
            min-width: 340px;
            max-width: 90%;
            z-index: 1000000;
            border-radius: 10px;
            padding: 14px 22px;
            font-weight: 600;
            box-shadow: var(--shadow-lg);
            display: none;
            text-align: center;
            font-size: 13.5px;
            border: 1.5px solid transparent;
        }
        #DatoObser.alert-danger { background: var(--danger-bg) !important; border-color: var(--danger-border) !important; color: #111 !important; }
        #DatoObser.alert-success { background: var(--success-bg) !important; border-color: var(--success-border) !important; color: #111 !important; }
        #DatoObser.alert-info { background: var(--info-bg) !important; border-color: var(--info-border) !important; color: #111 !important; }

        /* ===== Modales ===== */
        .modal-content { border-radius: 14px !important; border: none !important; box-shadow: var(--shadow-lg) !important; overflow: hidden; }
        .modal-header { background: #eef2f7; border-bottom: 1.5px solid var(--border) !important; padding: 16px 22px !important; }
        .modal-title { font-weight: 700; color: var(--text); font-size: 15px; display: inline-flex; align-items: center; gap: 9px; margin: 0; }
        .modal-title i { color: var(--primary); font-size: 18px; }
        .modal-body { padding: 22px !important; background: #f8fafc; }
        .modal-footer { padding: 14px 22px !important; background: #eef2f7; border-top: 1.5px solid var(--border) !important; display: flex; justify-content: flex-end; gap: 10px; }
        .modal-footer .btn { min-width: 110px; justify-content: center; }

        /* ===== Cajas de Formulario (.nc-box) ===== */
        .nc-box {
            background: #fff;
            border: 1.5px solid #b7c3d2;
            border-radius: 12px;
            padding: 18px 16px;
            box-shadow: 0 1px 3px rgba(11, 19, 34, .06);
            display: flex;
            flex-direction: column;
            gap: 12px;
            height: 100%;
            width: 100%;
        }
        .nc-box .nc-row { display: flex; gap: 10px; width: 100%; }
        .nc-box .nc-cell { min-width: 0; }
        .nc-box .nc-cell-4 { flex: 0 0 32%; }
        .nc-box .nc-cell-8 { flex: 1; }

        /* ===== Adjuntar archivo ===== */
        .btn-file.modern-file-input {
            display: flex !important;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px 20px !important;
            border: 2px dashed var(--danger-border) !important;
            background: #fef2f2 !important;
            color: var(--danger) !important;
            border-radius: 10px !important;
            width: 100%;
            font-weight: 600;
            font-size: 13px;
            transition: all .15s;
            cursor: pointer;
            text-align: center;
        }
        .btn-file.modern-file-input.archivo-cargado,
        .btn-file.modern-file-input.archivo-cargado * {
            border-color: var(--success-border) !important;
            background: var(--success-bg) !important;
            color: var(--success-hover) !important;
        }

        .nc-modal-center { display: flex; justify-content: center; width: 100%; padding: 4px 0; }
        .nc-modal-center .nc-box { max-width: 640px; width: 100%; padding: 22px 20px; gap: 16px; }

        .sin-resultados { text-align: center; padding: 44px 20px !important; color: var(--text-muted); font-size: 14px; }
        .sin-resultados i { font-size: 38px; color: var(--border-strong); display: block; margin-bottom: 10px; }
        .sin-resultados b { display: block; color: var(--text); font-size: 15px; margin-bottom: 4px; }

        .desc-hint { display: none; }
        .desc-modal-text { white-space: pre-wrap; word-break: break-word; font-size: 13.5px; line-height: 1.6; color: var(--text); }
        .ui-datepicker { z-index: 9999999 !important; }

        /* ===== RESPONSIVE MÓVIL ===== */
        @media (max-width: 767px) {
            body { font-size: 13px; }
            .container-main { width: 100%; padding: 10px 8px 0 8px; }
            .navbar-default { padding: 8px 12px; }
            .actions-box { width: 100%; height: auto; min-height: 48px; padding: 4px; gap: 6px; justify-content: center; }
            .btn-nav { width: 42px; height: 40px; font-size: 17px; }

            .tableFixHead { border: none; border-radius: 0; box-shadow: none; background: transparent; margin-bottom: 10px; }
            .tableFixHead thead { display: none; }
            .tableFixHead table, .tableFixHead tbody, .tableFixHead tr, .tableFixHead td { display: block; width: 100%; }
            .tableFixHead tbody tr {
                background: #fff !important;
                border: 1.5px solid var(--border);
                border-radius: 10px;
                padding: 12px 14px;
                margin-bottom: 10px;
                box-shadow: var(--shadow-sm);
                cursor: pointer;
            }
            .tableFixHead tbody tr td {
                padding: 6px 0 !important;
                border: none !important;
                text-align: left !important;
                font-size: 12.5px;
                word-break: break-word;
                background: transparent !important;
            }
            .tableFixHead tbody td::before {
                content: attr(data-label);
                display: block;
                font-size: 10px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .4px;
                color: var(--text-muted);
                margin-bottom: 2px;
            }
            .tableFixHead tbody td.sin-resultados::before { content: none; }
            .tableFixHead tbody tr td:last-child {
                border-top: 1px dashed var(--border) !important;
                padding-top: 10px !important;
                margin-top: 6px;
            }
            .tableFixHead tbody td.desc-cell .desc-text {
                display: -webkit-box;
                -webkit-box-orient: vertical;
                -webkit-line-clamp: 3;
                overflow: hidden;
                text-overflow: ellipsis;
                line-height: 1.4;
            }
            .tableFixHead tbody td.desc-cell .desc-hint {
                display: inline-block;
                margin-top: 4px;
                font-size: 10.5px;
                font-weight: 700;
                color: var(--primary);
                text-transform: uppercase;
            }
            .tableFixHead tbody td.desc-cell .desc-hint::after { content: 'Ver descripción completa ›'; }

            .nc-pagination { padding: 10px 12px; flex-direction: column; align-items: stretch; gap: 10px; }
            .nc-pagination-info { text-align: center; }
            .nc-pagination-controls { justify-content: center; }
            .nc-box .nc-row { flex-direction: column; gap: 8px; }
            .nc-box .nc-cell-4, .nc-box .nc-cell-8 { flex: 1 1 100%; width: 100%; }
        }
    </style>

    <script type="text/javascript">
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });

        $(function () {
            $('#DivDetectadaEnOtra, #DivRequiereAC, #DivNumeroNCAccionCorrectiva').hide();

            $.datepicker.regional['es'] = {
                closeText: 'Cerrar', prevText: '&#x3c;Ant', nextText: 'Sig&#x3e;', currentText: 'Hoy',
                monthNames: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
                monthNamesShort: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
                dayNames: ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'],
                dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sáb'],
                dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
                weekHeader: 'Sm', dateFormat: 'dd/mm/yy', firstDay: 1, isRTL: false,
                showMonthAfterYear: false, yearSuffix: ''
            };
            $.datepicker.setDefaults($.datepicker.regional['es']);

            $("#FApertura, #FRevision, #FCierre").datepicker({ dateFormat: 'dd-mm-yy', showOn: 'focus' });

            // Inicializar Select2 con búsqueda en tiempo real
            $('#FiltroCodGestion, #FiltroProceso, #FiltroTipo, #FiltroTipoAcciones, #Ordencito').select2({
                language: 'es',
                width: '100%',
                allowClear: false
            });

            $('#CodGestion, #Proceso, #Tipo, #DetectadaEn, #RequiereAC').select2({
                language: 'es',
                dropdownParent: $('#ModalEditarNoConformidad'),
                width: '100%'
            });

            $('#TipoReporte, #ReportProceso').select2({
                language: 'es',
                dropdownParent: $('#ModalReportes'),
                width: '100%'
            });

            // Calcular la altura exacta del Navbar para que el THEAD de la tabla quede pegado debajo
            function AjustarAlturaMenu() {
                var nav = document.querySelector('.navbar-default');
                if (!nav) return;
                if (window.innerWidth < 768 && nav.querySelector('.collapse.show')) {
                    return;
                }
                var alto = nav.offsetHeight;
                document.body.style.paddingTop = alto + 'px';
                document.documentElement.style.setProperty('--navbar-height', alto + 'px');
            }
            AjustarAlturaMenu();
            $(window).on('load resize', AjustarAlturaMenu);
            $('#collapseFiltrosNav').on('hidden.bs.collapse', AjustarAlturaMenu);
            $(document).on('shown.bs.modal hidden.bs.modal', AjustarAlturaMenu);
        });

        function MostrarNombreArchivo(input) {
            var span = document.getElementById('SpanFileNoConformidad');
            if (!span) return;
            if (input.files && input.files.length > 0) {
                var nombre = input.files[0].name;
                if (nombre.length > 45) { nombre = nombre.substring(0, 42) + '...'; }
                span.innerHTML = '<i class="bi bi-file-earmark-check"></i> ' + nombre;
                var contenedor = document.getElementById('BtnFileNoConformidad');
                if (contenedor) contenedor.classList.add('archivo-cargado');
            } else {
                span.innerHTML = '<i class="bi bi-file-earmark-pdf"></i> Adjuntar archivo PDF (máx. 4MB)';
                var contenedor2 = document.getElementById('BtnFileNoConformidad');
                if (contenedor2) contenedor2.classList.remove('archivo-cargado');
            }
        }

        function ResetearArchivoNC() {
            var span = document.getElementById('SpanFileNoConformidad');
            var input = document.getElementById('FileNoConformidad');
            var btn = document.getElementById('BtnFileNoConformidad');
            if (span) { span.innerHTML = '<i class="bi bi-file-earmark-pdf"></i> Adjuntar archivo PDF (máx. 4MB)'; }
            if (btn) { btn.classList.remove('archivo-cargado'); }
            if (input) { input.value = ''; input.dataset.yacargado = 0; }
        }

        function MarcarArchivoYaCargado() {
            var span = document.getElementById('SpanFileNoConformidad');
            var input = document.getElementById('FileNoConformidad');
            var btn = document.getElementById('BtnFileNoConformidad');
            if (span) { span.innerHTML = '<i class="bi bi-file-earmark-check"></i> Archivo PDF ya cargado — seleccione uno nuevo para reemplazar'; }
            if (btn) { btn.classList.add('archivo-cargado'); }
            if (input) input.dataset.yacargado = 1;
        }

        function MostrarDatoObser(msg, mTipo) {
            var el = document.getElementById('DatoObser');
            var cls = 'alert-danger';
            if (mTipo === true || mTipo === 'success') { cls = 'alert-success'; }
            else if (mTipo === 'info' || msg === 'Procesando') { cls = 'alert-info'; }
            el.className = 'myAlert-top ' + cls;
            if (msg) { el.innerHTML = msg; }
            $("#DatoObser").show();
            setTimeout(function () { $("#DatoObser").hide(); }, 3000);
        }

        function ValidarFiltros() {
            document.FrmFiltrar.submit();
        }

        function ScrollAlPrimerError(contenedor) {
            var $err = $(contenedor).find('.alert-danger').filter(':visible').first();
            if ($err.length) {
                var dom = $err[0];
                try { dom.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
                catch (e) { dom.scrollIntoView(); }
                setTimeout(function () { try { dom.focus(); } catch (e) {} }, 250);
            }
        }

        function EsVistaMovil() { return window.matchMedia && window.matchMedia('(max-width: 767px)').matches; }

        function AbrirModalDescripcion(cell) {
            var $txt = $(cell).find('.desc-contenido');
            if (!$txt.length) { return; }
            $('#ModalDescripcionNoConformidadBody').html($txt.html());
            $('#ModalDescripcionNoConformidad').modal('show');
        }

        $(document).on('click', '#TBodyNoConformidad tr', function (e) {
            if (!EsVistaMovil()) { return; }
            var $tr = $(this);
            if ($tr.find('td.sin-resultados').length) { return; }
            if ($(e.target).closest('a, button, .acciones-cell, .fecha-cierre').length) { return; }
            AbrirModalDescripcion($tr.find('.desc-cell'));
        });

        function ValidarDetectadaEn(Valorcito) {
            if (Valorcito == "Otra" || Valorcito == "Quejas") {
                document.FrmNoConformidad.DetectadaEnOtra.value = '';
                $('#DivDetectadaEnOtra').show();
            } else { $('#DivDetectadaEnOtra').hide(); }
        }

        function MostrarDivConsultaNoConformidad(mNumeroNC) {
            $("#ModalConsultaNoConformidadBody").load(
                "{{ url('/noconformidades/mostrar') }}?NumeroNC=" + mNumeroNC,
                function () { $("#ModalConsultaNoConformidad").modal('show'); }
            );
        }

        function MostrarDivEditarNoConformidad(mNumeroNC) {
            document.FrmNoConformidad.reset();
            document.getElementById('NumeroNC').value = mNumeroNC;
            $("#FrmNoConformidad").find("input:text,select,textarea").removeClass("alert-danger");
            $("#BotsNoConformidad").show();
            ResetearArchivoNC();
            if (mNumeroNC == 'Nuevo') {
                document.getElementById('Responsable').value = '{{ $NomUsuario }}';
                document.getElementById('FApertura').value = '{{ date("d/m/Y") }}';
                $('#DivDetectadaEnOtra, #DivRequiereAC, #DivNumeroNCAccionCorrectiva').hide();
                document.getElementById('Proceso').length = 1;
                $('#CodGestion, #Proceso, #Tipo, #DetectadaEn, #RequiereAC').val('').trigger('change');
                $("#ModalEditarNoConformidad").modal('show');
            } else {
                $.ajax({
                    type: "GET",
                    url: "{{ url('/noconformidades/editar') }}",
                    data: { NumeroNC: mNumeroNC },
                    dataType: "json",
                    cache: false
                }).done(function (data) {
                    if (data.status === "Error") { MostrarDatoObser("Se presentó un error"); return false; }
                    $('#CodGestion').val(data.CodGestion).trigger('change');
                    CambioGestionProceso(data.Proceso);
                    document.getElementById('Responsable').value = data.NomResponsable;
                    $('#Tipo').val(data.Tipo).trigger('change');
                    document.getElementById('FApertura').value = data.FApertura;
                    
                    var detectadaVal = data.DetectadaEn;
                    if ($('#DetectadaEn option[value="' + detectadaVal + '"]').length === 0 && detectadaVal !== '') {
                        detectadaVal = 'Otra';
                    }
                    $('#DetectadaEn').val(detectadaVal).trigger('change');
                    ValidarDetectadaEn(detectadaVal);

                    document.getElementById('DetectadaEnOtra').value = data.DetectadaEnOtra || '';
                    document.getElementById('Descripcion').value = data.Descripcion || '';
                    document.getElementById('Correccion').value = data.Correccion || '';
                    $('#RequiereAC').val(data.RequiereAC).trigger('change');
                    CambioTipo();
                    document.getElementById('NumeroNCAccionCorrectiva').value = data.NumeroNCAccionCorrectiva || '';
                    document.getElementById('Causas').value = data.Causas || '';
                    document.getElementById('Accion').value = data.Accion || '';
                    document.getElementById('FRevision').value = data.FRevision || '';
                    if (data.YaCargado === 1) { MarcarArchivoYaCargado(); }
                    $("#ModalEditarNoConformidad").modal('show');
                });
            }
        }

        function GrabarNoConformidad() {
            MostrarDatoObser('Procesando');
            mNumeroNC = document.FrmNoConformidad.NumeroNC.value;
            $("#BotsNoConformidad").hide();
            mRetorno = true;
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
                var input = document.getElementById("FileNoConformidad");
                if (input.files.length > 0) {
                    if (!(input.files[0].type == "application/pdf")) { $("#BotsNoConformidad").show(); MostrarDatoObser("¡Error! Solo se admiten archivos en formato PDF"); return false; }
                    if (input.files[0].size > 4 * 1024 * 1024) { $("#BotsNoConformidad").show(); MostrarDatoObser("¡Error! El archivo supera el tamaño máximo permitido (4MB)"); return false; }
                }
            }
            if (mRetorno) {
                document.FrmNoConformidad.TipoGrabar.value = 'GrabarNoConformidad';
                var f = new FormData(document.getElementById('FrmNoConformidad'));
                $.ajax({
                    url: "{{ url('/noconformidades/grabar') }}", type: 'post', cache: false, data: f,
                    dataType: "json", processData: false, contentType: false
                }).done(function (response) {
                    if (response.status === 'success') {
                        MostrarDatoObser("Los datos se grabaron.", true);
                        $("#FiltroNumeroNC").val(response.id);
                        setTimeout(function () { document.FrmFiltrar.submit(); }, 500);
                    } else { $("#BotsNoConformidad").show(); MostrarDatoObser(response.message || "No se pudo guardar el registro."); }
                }).fail(function () { $("#BotsNoConformidad").show(); MostrarDatoObser("Error de conexión o formato inválido en el servidor."); });
            } else {
                $("#BotsNoConformidad").show();
                MostrarDatoObser('<i class="bi bi-exclamation-triangle"></i> Complete los campos resaltados en rojo antes de continuar.');
                ScrollAlPrimerError('#FrmNoConformidad');
            }
        }

        function MostrarDivSeguimientos(mNumeroNCSeguimientos) {
            document.FrmSeguimientos.reset();
            document.getElementById('NumeroNCSeguimientos').value = mNumeroNCSeguimientos;
            $("#BotsSeguimientos").show();
            $("#FrmSeguimientos").find("input:text,select,textarea").removeClass("alert-danger");
            $("#DivSeguimientos").load(
                "{{ url('/noconformidades/seguimientos') }}?NumeroNC=" + mNumeroNCSeguimientos,
                function () {
                    $("#ModalSeguimientos").modal('show');
                    $("#DivSeguimientos input[id^='Fecha']").datepicker({ dateFormat: 'dd-mm-yy', showOn: 'focus' });
                }
            );
        }

        function BorrarSeguimiento(Obj) {
            var btn = Obj.closest('button') || Obj.parentElement;
            var mID = btn.id.replace("BotBorrarSeguimiento", "");
            document.getElementById('Borrada' + mID).value = 1;
            $("#DivSeguimiento" + mID).hide();
        }

        function AgregarSeguimiento() {
            mRegNew = parseInt(document.getElementById('CantSeguimientos').value, 10);
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

            var $inputFecha = $nuevoBloque.find("input[id^='Fecha']");
            $inputFecha.removeClass('hasDatepicker').removeData('datepicker').unbind();

            $nuevoBloque.find('#SpanSeguimiento' + mRegNew).text(mRegNew);
            $nuevoBloque.attr("id", "DivSeguimiento" + mRegNew);
            $nuevoBloque.show();
            $nuevoBloque.appendTo('#DivSeguimientos');

            $("#Fecha" + mRegNew).datepicker({ dateFormat: 'dd/mm/yy', showOn: 'focus' });
        }

        function GrabarSeguimientos() {
            MostrarDatoObser('Procesando');
            mNumeroNCSeguimientos = document.getElementById('NumeroNCSeguimientos').value;
            $("#BotsSeguimientos").hide();
            mRetorno = true;
            mCantSeguimientos = document.getElementById('CantSeguimientos').value;
            var mAlMenosUno = false;
            for (NumSeguimiento = 1; NumSeguimiento <= mCantSeguimientos; NumSeguimiento++) {
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
                    url: "{{ url('/noconformidades/seguimientos') }}", type: 'post', cache: false, data: f,
                    dataType: "json", processData: false, contentType: false
                }).done(function (response) {
                    if (response.status === 'success') {
                        MostrarDatoObser("Los datos se grabaron.", true);
                        setTimeout(function () { location.reload(); }, 500);
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
            $("#BotsCerrarNoConformidad").show();
            $("#FrmCerrarNoConformidad").find("input:text,select,textarea").removeClass("alert-danger");
            $.ajax({
                type: "GET", url: "{{ url('/noconformidades/cierre') }}",
                data: { NumeroNC: mNumeroNCCerrar },
                cache: false
            }).done(function (html) {
                if (html == "Error") { MostrarDatoObser("Se presentó un error"); return false; }
                else { eval(html); $("#ModalCerrarNoConformidad").modal('show'); }
            });
        }

        function GrabarCerrarNoConformidad() {
            MostrarDatoObser('Procesando');
            mNumeroNCCerrar = document.getElementById('NumeroNCCerrar').value;
            $("#BotsCerrarNoConformidad").hide();
            mRetorno = true;
            $("#ResultadoRevision, #MotivoCierre, #NuevaAccion, #FCierre, #RCierre").each(function () {
                $(this).removeClass("alert-danger");
                if (!$(this).val() || $(this).val().trim() === '') { mRetorno = false; $(this).addClass("alert-danger"); }
            });
            if (mRetorno) {
                document.FrmCerrarNoConformidad.TipoGrabar.value = 'CerrarNoConformidad';
                var f = new FormData(document.getElementById('FrmCerrarNoConformidad'));
                $.ajax({
                    url: "{{ url('/noconformidades/cierre') }}", type: 'post', cache: false, data: f,
                    dataType: "json", processData: false, contentType: false
                }).done(function (response) {
                    if (response.status === 'success') {
                        MostrarDatoObser("Los datos se grabaron.", true);
                        setTimeout(function () { location.reload(); }, 500);
                    } else { $("#BotsCerrarNoConformidad").show(); MostrarDatoObser(response.message || "Error al guardar."); }
                });
            } else {
                $("#BotsCerrarNoConformidad").show();
                MostrarDatoObser('<i class="bi bi-exclamation-triangle"></i> Complete los campos resaltados en rojo antes de continuar.');
                ScrollAlPrimerError('#FrmCerrarNoConformidad');
            }
        }

        let ultimaPeticionProceso = 0;
        let procesoPendiente = '';
        function CambioGestionProceso(mProceso) {
            if (typeof mProceso === 'undefined') mProceso = '';
            const sel = document.getElementById('Proceso');
            const mCodGestion = document.getElementById('CodGestion').value;
            if (mProceso) procesoPendiente = mProceso;
            const seleccionado = procesoPendiente || sel.value;
            const id = ++ultimaPeticionProceso;
            sel.length = 1;
            if (!mCodGestion) { procesoPendiente = ''; $('#Proceso').val('').trigger('change'); return; }
            $.ajax({
                type: "GET", url: "{{ url('/noconformidades/procesos') }}", dataType: "json", cache: false,
                data: { CodGestion: mCodGestion }
            }).done(function (r) {
                if (id !== ultimaPeticionProceso) return;
                if (r.status !== 'success') { MostrarDatoObser(r.message || "Se presentó un error"); return; }
                sel.length = 1;
                r.procesos.forEach(p => sel.add(new Option(p.nombre, p.codigo)));
                if (seleccionado && Array.from(sel.options).some(o => o.value === seleccionado)) { sel.value = seleccionado; }
                procesoPendiente = '';
                $('#Proceso').trigger('change');
            });
        }

        function CambioTipo() {
            mCodTipo = document.getElementById('Tipo').value;
            mRequiereAC = document.getElementById('RequiereAC').value;
            if (mCodTipo == 'Corrección') {
                $("#DivRequiereAC").show();
                if (mRequiereAC == '-1') { $("#DivNumeroNCAccionCorrectiva").show(); }
                else { $("#DivNumeroNCAccionCorrectiva").hide(); }
            } else { $("#DivRequiereAC, #DivNumeroNCAccionCorrectiva").hide(); }
        }

        function MostrarDivReportes() { $("#ModalReportes").modal('show'); }

        function EnviarReportes() {
            mRetorno = true;
            $("#TipoReporte").removeClass("alert-danger");
            if (!$("#TipoReporte").val()) { mRetorno = false; $("#TipoReporte").addClass("alert-danger"); }
            if (!mRetorno) {
                MostrarDatoObser('<i class="bi bi-exclamation-triangle"></i> Seleccione el tipo de reporte antes de generar.');
                ScrollAlPrimerError('#FrmReportes'); return;
            }
            MostrarDatoObser('Procesando');
            var $btnGenerar = $("#BtnGenerarReporte");
            $btnGenerar.prop('disabled', true);
            (async () => {
                try {
                    const url = "{{ url('/noconformidades/reportes') }}?TipoReporte=" + encodeURIComponent($("#TipoReporte").val()) +
                                "&ReportProceso=" + encodeURIComponent($("#ReportProceso").val() || '');
                    const response = await fetch(url);
                    if (!response.ok) { throw new Error('Error HTTP ' + response.status); }
                    const raw_data = await response.json();
                    if (!Array.isArray(raw_data)) {
                        MostrarDatoObser('<i class="bi bi-exclamation-triangle"></i> ' + (raw_data.error || 'No se pudo generar el reporte.')); return;
                    }
                    if (raw_data.length < 2 || !raw_data[0] || !raw_data[0]['Campos']) {
                        MostrarDatoObser('<i class="bi bi-exclamation-triangle"></i> No hay datos para el reporte con los filtros seleccionados.'); return;
                    }
                    let mCampos = raw_data[0];
                    raw_data.shift();
                    const prez = raw_data;
                    let rows = [];
                    if (prez.length === 0) {
                        MostrarDatoObser('<i class="bi bi-exclamation-triangle"></i> No hay datos para el reporte con los filtros seleccionados.'); return;
                    }
                    eval(mCampos['Campos']);
                    const worksheet = XLSX.utils.json_to_sheet(rows);
                    eval(mCampos['Formato']);
                    const workbook = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(workbook, worksheet, "Datos");
                    XLSX.writeFile(workbook, "datos.xlsx", { compression: true });
                    MostrarDatoObser('<i class="bi bi-check-circle"></i> Reporte generado correctamente.', true);
                } catch (err) {
                    MostrarDatoObser('<i class="bi bi-exclamation-triangle"></i> Error al generar el reporte: ' + err.message);
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
                    type: "POST", url: "{{ url('/noconformidades/reversar') }}",
                    data: {
                        TipoGrabar: 'ReversarNoConformidad', NumeroNC: mNumeroNC
                    },
                    dataType: 'json', cache: false
                }).done(function (response) {
                    if (response.status === 'success') {
                        MostrarDatoObser("No conformidad reversada exitosamente.", true);
                        setTimeout(function () { location.reload(); }, 1000);
                    } else { MostrarDatoObser("Error al reversar: " + (response.message || '')); }
                });
            }
        }
    </script>
</head>

<body style="padding-top: 112px;" @if(request('DatoObser')) onLoad="MostrarDatoObser('{{ request('DatoObser') }}', true);" @endif>

<div class="myAlert-top" id="DatoObser" style="display:none"><b>Danger!</b> ...</div>

<!-- NAVBAR / FILTROS FIJA EN 2 NIVELES (IDÉNTICA A LA CAPTURA) -->
<nav class="navbar-default fixed-top" role="navigation">
    <!-- Nivel 1: Título e ícono -->
    <div class="navbar-top-bar">
        <a class="navbar-brand-custom" href="{{ url('/') }}">
            <span class="brand-icon"><i class="bi bi-clipboard-check"></i></span>
            <span>No Conformidades & Mejoras
                <span class="label-xsmall">Versión 2 &nbsp;·&nbsp; Código PGGC-04-R-01</span>
            </span>
        </a>
        <button class="btn btn-outline-secondary d-md-none btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFiltrosNav">
            <i class="bi bi-list"></i>
        </button>
    </div>

    <!-- Nivel 2: Fila de Filtros y Acciones -->
    <div class="collapse d-md-block" id="collapseFiltrosNav">
        <form method="get" action="{{ url('/') }}" class="form_validar" name="FrmFiltrar" id="FrmFiltrar">
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
                        <div class="col-6 col-md-2" title="Tipo de Registro">
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
                        <button class="btn-nav" type="button" onClick="ValidarFiltros();" title="Filtrar datos"><i class="bi bi-search"></i></button>
                        <a onClick="MostrarDivEditarNoConformidad('Nuevo');" class="btn-nav btn-nav-primary" title="Crear registro"><i class="bi bi-plus-lg"></i></a>
                        <button class="btn-nav" type="button" onClick="MostrarDivReportes();" title="Generar Reportes"><i class="bi bi-file-earmark-excel"></i></button>
                        <a href="{{ url('/') }}" class="btn-nav" title="Regresar"><i class="bi bi-house"></i></a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</nav>

<div class="container-main">
    <!-- TABLA + PAGINACIÓN -->
    @if($filtrar == 1)
    <div class="row sidenav">
        <div class="col-12">
            <div class="tableFixHead table-responsive">
                <table class="table table-striped table-bordered small">
                    <thead>
                        <tr>
                            <th width="10%">Fecha / ID solicitud</th>
                            <th width="17%">Responsable Apertura</th>
                            <th width="7%">Proceso</th>
                            <th width="46%">Descripción</th>
                            <th width="10%">Detectada en</th>
                            <th width="10%">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="TBodyNoConformidad">
                        @forelse($noConformidades as $Row)
                            <tr>
                                <td data-label="Fecha / ID SOLICITUD">
                                    {{ \App\Http\Controllers\NoConformidadController::darFechaHora($Row->FApertura, 1) }}
                                    (<a class="link-modern" style="cursor:pointer" onClick="MostrarDivConsultaNoConformidad({{ $Row->NumeroNC }});">{{ $Row->NumeroNC }}</a>)
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
                                            @if($PuedeReversar)
                                                <a class="action-btn action-danger" onClick="ReversarNoConformidad({{ $Row->NumeroNC }})" title="Reversar no conformidad"><i class="bi bi-arrow-counterclockwise"></i></a>
                                            @endif
                                        @else
                                            <a class="action-btn action-edit" onClick="MostrarDivEditarNoConformidad({{ $Row->NumeroNC }});" title="Editar"><i class="bi bi-pencil-square"></i></a>
                                            <a class="action-btn action-list" onClick="MostrarDivSeguimientos({{ $Row->NumeroNC }});" title="Seguimientos"><i class="bi bi-list-check"></i></a>
                                            @if($PuedeCerrar)
                                                <a class="action-btn action-success" onClick="MostrarDivCerrarNoConformidad({{ $Row->NumeroNC }})" title="Cerrar registro"><i class="bi bi-check2-circle"></i></a>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="sin-resultados">
                                    <i class="bi bi-inbox"></i>
                                    <b>No se encontraron registros con los criterios actuales.</b>
                                    <small>Modifique los filtros o use <b>"N° reg"</b> para buscar por número.</small>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- BARRA DE PAGINACIÓN -->
            @if(isset($totalRegistros) && $totalRegistros > 0)
            @php
                $urlPagina = function ($n) {
                    return request()->fullUrlWithQuery(['Filtrar' => 1, 'pagina' => $n]);
                };
                $rangoPag = 2;
                $inicioPag = max(1, $paginaActual - $rangoPag);
                $finPag = min($totalPaginas, $paginaActual + $rangoPag);
            @endphp
            <div class="nc-pagination">
                <div class="nc-pagination-info">
                    Mostrando {{ $offset + 1 }}&ndash;{{ min($offset + $regPorPagina, $totalRegistros) }} de {{ $totalRegistros }} registros
                </div>
                <div class="nc-pagination-controls">
                    <a class="page-btn {{ $paginaActual <= 1 ? 'disabled' : '' }}" href="{{ $paginaActual > 1 ? $urlPagina($paginaActual - 1) : '#' }}">&laquo; Anterior</a>

                    @if($inicioPag > 1)
                        <a class="page-btn" href="{{ $urlPagina(1) }}">1</a>
                        @if($inicioPag > 2)
                            <span class="page-ellipsis">&hellip;</span>
                        @endif
                    @endif

                    @for($p = $inicioPag; $p <= $finPag; $p++)
                        <a class="page-btn {{ $p == $paginaActual ? 'active' : '' }}" href="{{ $urlPagina($p) }}">{{ $p }}</a>
                    @endfor

                    @if($finPag < $totalPaginas)
                        @if($finPag < $totalPaginas - 1)
                            <span class="page-ellipsis">&hellip;</span>
                        @endif
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

<!-- ===================== MODALES ===================== -->

<!-- MODAL CONSULTA -->
<div class="modal fade" id="ModalConsultaNoConformidad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" style="max-width:98%">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ModalConsultaNoConformidadTitle"><i class="bi bi-eye"></i> Consulta Registro de No Conformidad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row" id="ModalConsultaNoConformidadBody">...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="BotCancelarModal" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i> Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DESCRIPCIÓN (MÓVIL) -->
<div class="modal fade" id="ModalDescripcionNoConformidad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" style="width:98%;max-width:640px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-file-text"></i> Descripción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="ModalDescripcionNoConformidadBody" class="desc-modal-text"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i> Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL EDITAR/CREAR -->
<div class="modal fade" id="ModalEditarNoConformidad" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" style="max-width:98%">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="ModalEditarNoConformidadTitle"><i class="bi bi-pencil-square"></i> Editar / Crear No Conformidad</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="DivBodyNoConformidad">
                <form method="post" enctype="multipart/form-data" class="form_validar" name="FrmNoConformidad" id="FrmNoConformidad" autocomplete="off">
                    <input name="TipoGrabar" type="hidden" id="TipoGrabar">
                    <input name="TipoModificar" type="hidden" id="TipoModificar">
                    <input name="NumeroNC" type="hidden" id="NumeroNC">

                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="nc-box">
                                <div class="form-group">
                                    <label for="Responsable">Responsable de la apertura</label>
                                    <input type="text" name="Responsable" id="Responsable" class="form-control" readonly>
                                </div>
                                <div class="nc-row">
                                    <div class="nc-cell nc-cell-4">
                                        <label for="Tipo">Tipo</label>
                                        <select name="Tipo" class="form-control" id="Tipo" onChange="CambioTipo()">
                                            <option value="">...</option>
                                            @foreach($mTipoRegistro as $Clave => $Valor)
                                                <option value="{{ $Clave }}">{{ $Clave }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="nc-cell nc-cell-8">
                                        <label for="CodGestion">Gestión</label>
                                        <select name="CodGestion" class="form-control" id="CodGestion" onChange="CambioGestionProceso();">
                                            <option value=""></option>
                                            @foreach($gestiones as $RowGes)
                                                <option value="{{ $RowGes->CodGestion }}">{{ $RowGes->NomGestion }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="Proceso">Proceso en donde se detecta</label>
                                    <select name="Proceso" class="form-control" id="Proceso"><option value="" selected>...</option></select>
                                </div>
                                <div class="form-group">
                                    <label for="FApertura">Fecha de apertura</label>
                                    <input type="text" name="FApertura" id="FApertura" class="form-control" placeholder="dd/mm/aaaa" onChange="ValidarFecha(this)" onBlur="ValidarFecha(this)">
                                </div>
                                <div class="nc-row">
                                    <div class="nc-cell nc-cell-8">
                                        <label for="DetectadaEn">Detectada en</label>
                                        <select name="DetectadaEn" class="form-control" id="DetectadaEn" onChange="ValidarDetectadaEn(this.value);">
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
                                    <div class="nc-cell nc-cell-4" id="DivDetectadaEnOtra">
                                        <label for="DetectadaEnOtra">Cual?</label>
                                        <input name="DetectadaEnOtra" class="form-control" id="DetectadaEnOtra" maxlength="30">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="nc-box">
                                <div class="form-group">
                                    <label for="Descripcion">Descripción</label>
                                    <textarea name="Descripcion" id="Descripcion" class="form-control" placeholder="Descripción" rows="3"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="Correccion">Relación de actividades</label>
                                    <textarea name="Correccion" id="Correccion" class="form-control" placeholder="Describa las actividades que se desarrollaron o están desarrollando para evitar o potencializar lo enunciado en el campo Descripción" rows="3"></textarea>
                                </div>
                                <div class="nc-row">
                                    <div class="nc-cell nc-cell-8" id="DivRequiereAC">
                                        <label for="RequiereAC">¿Con la acción determinada se garantiza que la falla no se presente de nuevo?</label>
                                        <select name="RequiereAC" class="form-control" id="RequiereAC" onChange="CambioTipo();">
                                            <option value="">...</option>
                                            <option value="1">Si</option>
                                            <option value="-1">No</option>
                                        </select>
                                    </div>
                                    <div class="nc-cell nc-cell-4" id="DivNumeroNCAccionCorrectiva">
                                        <label for="NumeroNCAccionCorrectiva">ID acción correctiva</label>
                                        <input name="NumeroNCAccionCorrectiva" class="form-control" id="NumeroNCAccionCorrectiva"
                                               maxlength="11" inputmode="numeric" onInput="SoloDigitos(this)">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="nc-box">
                                <div class="form-group">
                                    <label for="Causas">Causas o Ventajas</label>
                                    <textarea name="Causas" id="Causas" class="form-control" placeholder="Causas o Ventajas" rows="3"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="Accion">Objetivo principal del plan de Acción</label>
                                    <textarea name="Accion" id="Accion" class="form-control" placeholder="Describa el objetivo del plan de acción implementado o a implementar. ¿Qué quiero lograr?" rows="3"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="FRevision">Fecha acordada para la revisión</label>
                                    <input type="text" name="FRevision" id="FRevision" class="form-control" placeholder="dd/mm/aaaa" onChange="ValidarFecha(this)" onBlur="ValidarFecha(this)">
                                </div>
                                <div class="form-group">
                                    <label class="btn btn-default btn-file modern-file-input"
                                           id="BtnFileNoConformidad"
                                           for="FileNoConformidad"
                                           style="display:flex!important;cursor:pointer;margin-bottom:0;">
                                        <span id="SpanFileNoConformidad"><i class="bi bi-file-earmark-pdf"></i> Adjuntar archivo PDF (máx. 4MB)</span>
                                    </label>
                                    <input name="FileNoConformidad" id="FileNoConformidad" type="file"
                                           accept="application/pdf"
                                           onChange="MostrarNombreArchivo(this)"
                                           style="display:none;">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer" id="BotsNoConformidad">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i> Cerrar</button>
                <button type="button" class="btn btn-success" onClick="GrabarNoConformidad();"><i class="bi bi-check2"></i> Aceptar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL SEGUIMIENTOS -->
<div class="modal fade" id="ModalSeguimientos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl" style="max-width:98%">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="ModalSeguimientosTitle"><i class="bi bi-list-check"></i> Editar Seguimientos No Conformidad</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="DivBodySeguimientos">
                <form method="post" enctype="multipart/form-data" class="form_validar" name="FrmSeguimientos" id="FrmSeguimientos" autocomplete="off">
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
            <div class="modal-header">
                <h3 class="modal-title" id="ModalCerrarNoConformidadTitle"><i class="bi bi-check2-circle"></i> Datos del Cierre del Registro</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="DivBodyCerrarNoConformidad">
                <form method="post" enctype="multipart/form-data" class="form_validar" name="FrmCerrarNoConformidad" id="FrmCerrarNoConformidad" autocomplete="off">
                    <input name="TipoGrabar" type="hidden" id="TipoGrabar">
                    <input name="TipoModificar" type="hidden" id="TipoModificar">
                    <input name="NumeroNCCerrar" type="hidden" id="NumeroNCCerrar">

                    <div class="row g-3">
                        <div class="col-md-6 form-group">
                            <label for="ResultadoRevision">Resultados de la revisión de la eficacia</label>
                            <textarea name="ResultadoRevision" id="ResultadoRevision" class="form-control small" placeholder="Resultados de la revisión de la eficacia de las acciones tomadas" rows="3"></textarea>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="MotivoCierre">Motivos del Cierre</label>
                            <textarea name="MotivoCierre" id="MotivoCierre" class="form-control small" placeholder="Motivos del Cierre" rows="3"></textarea>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="NuevaAccion">Nueva Acción</label>
                            <textarea name="NuevaAccion" id="NuevaAccion" class="form-control small" placeholder="Nueva Acción?" rows="3"></textarea>
                        </div>
                        <div class="col-md-2 form-group">
                            <label for="FCierre">Fecha del Cierre</label>
                            <input type="text" name="FCierre" id="FCierre" class="form-control" placeholder="dd/mm/aaaa" onChange="ValidarFecha(this)" onBlur="ValidarFecha(this)">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="RCierre">Responsable del Cierre</label>
                            <input type="text" name="RCierre" id="RCierre" class="form-control">
                        </div>
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
            <div class="modal-header">
                <h5 class="modal-title" id="ModalReportesTitle"><i class="bi bi-file-earmark-excel"></i> Consulta Reportes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="ModalReportesBody">
                <form method="post" enctype="multipart/form-data" class="form_validar" name="FrmReportes" id="FrmReportes" autocomplete="off" target="_blank" style="width:100%">
                    <input name="TipoGrabar" type="hidden" id="TipoGrabar">
                    <input name="TipoModificar" type="hidden" id="TipoModificar">

                    <div class="nc-modal-center">
                        <div class="nc-box">
                            <div class="form-group">
                                <label for="TipoReporte">Tipo Reporte</label>
                                <select name="TipoReporte" class="form-control" id="TipoReporte" onChange="CambioTipoReporte(this);">
                                    <option value="">...</option>
                                    <option value="ReporteNoConformidades">Todos los registros</option>
                                </select>
                            </div>
                            <div class="form-group" id="DivFechasReporte">
                                <label for="ReportProceso">Proceso</label>
                                <select name="ReportProceso" id="ReportProceso" class="form-control">
                                    <option value="">Todos los procesos</option>
                                    @foreach($procesos as $RowProc)
                                        @php $codComp = $RowProc->Colegio . $RowProc->CodProceso; @endphp
                                        <option value="{{ $codComp }}" {{ request('FiltroProceso') == $codComp ? 'selected' : '' }}>{{ $RowProc->CodGestion . ' ' . $RowProc->NomProceso }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer" id="BotsReportes">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i> Cerrar</button>
                <button type="button" class="btn btn-success" id="BtnGenerarReporte" onClick="EnviarReportes();"><i class="bi bi-file-earmark-arrow-down"></i> Generar</button>
            </div>
        </div>
    </div>
</div>

</body>
</html>