// public/js/global.js

// Función global para mostrar alertas
function MostrarDatoObser(msg, mTipo) {
    var el = document.getElementById('DatoObser');
    if (!el) return;
    var cls = (mTipo === true || mTipo === 'success') ? 'alert-success' : (mTipo === 'info' ? 'alert-info' : 'alert-danger');
    el.className = 'myAlert-top ' + cls; 
    if (msg) el.innerHTML = msg;
    $("#DatoObser").show(); 
    setTimeout(() => $("#DatoObser").hide(), 3000);
}