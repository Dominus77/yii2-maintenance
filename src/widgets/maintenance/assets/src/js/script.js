$(function () {
    function initMaintenanceForm(prop) {
        let maintenance = $('#filestateform-mode'),
            settings = $('#maintenance-setting-container'),
            on = prop.modeOn,
            off = prop.modeOff;

        function toggleSettings(mode) {
            if (mode === off) {
                settings.hide('slow');
            }
            if (mode === on) {
                settings.show('slow');
            }
        }

        toggleSettings(maintenance.val());

        maintenance.on('change', function () {
            toggleSettings(this.value);
        });

        setTimeout(function () {
            $('.notify').fadeOut(2000, 'swing');
        }, 3000);
    }

    window.initMaintenanceForm = initMaintenanceForm;
});