/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

if (typeof (akeeba) == "undefined")
{
    var akeeba = {};
}

if (typeof akeeba.Browser == "undefined")
{
    akeeba.Browser = {
        useThis: function () {
            var rawFolder = document.forms.adminForm.folderraw.value;

            if (rawFolder === "[SITEROOT]")
            {
                rawFolder = "[SITETMP]";

                alert(akeeba.System.Text._("COM_AKEEBA_CONFIG_UI_ROOTDIR"));
            }

            window.parent.akeeba.Configuration.onBrowserCallback(rawFolder);
        }
    };
}

akeeba.Loader.add('akeeba.System', function () {
    akeeba.System.addEventListener('comAkeebaBrowserUseThis', 'click', function () {
        akeeba.Browser.useThis();

        return false;
    });
    akeeba.System.addEventListener('comAkeebaBrowserGo', 'click', function () {
        document.form.adminForm.submit();

        return false;
    });
});