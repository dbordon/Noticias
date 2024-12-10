/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Object initialisation
if (typeof akeeba == "undefined")
{
    var akeeba = {};
}

if (typeof akeeba.ControlPanel == "undefined")
{
    akeeba.ControlPanel = {
        needsDownloadID:        true,
        outputDirUnderSiteRoot: false,
        hasSecurityFiles:       false
    }
}

/**
 * Displays the changelog in a popup box
 */
akeeba.ControlPanel.showChangelog = function () {
    akeeba.Loader.add('akeeba.Modal', function () {
        akeeba.Modal.open({
            inherit: "#akeeba-changelog",
            width:   "80%"
        });
    });
};

akeeba.ControlPanel.checkOutputFolderSecurity = function () {
    if (!akeeba.System.getOptions("akeeba.ControlPanel.outputDirUnderSiteRoot", false))
    {
        return;
    }

    akeeba.System.doAjax({
            ajaxURL: "index.php?option=com_akeeba&view=ControlPanel&task=checkOutputDirectory&format=raw"
        }, function (data) {
            var readFile   = data.hasOwnProperty("readFile") ? data.readFile : false;
            var listFolder = data.hasOwnProperty("listFolder") ? data.listFolder : false;
            var isSystem   = data.hasOwnProperty("isSystem") ? data.isSystem : false;
            var hasRandom  = data.hasOwnProperty("hasRandom") ? data.hasRandom : true;

            if (listFolder && isSystem)
            {
                document.getElementById("outDirSystem").style.display = "block";
            }
            else if (listFolder)
            {
                document.getElementById("insecureOutputDirectory").style.display = "block";
            }
            else if (readFile && !listFolder && !hasRandom)
            {
                if (!akeeba.System.getOptions("akeeba.ControlPanel.hasSecurityFiles", true))
                {
                    document.getElementById("insecureOutputDirectory").style.display = "block";

                    return;
                }

                if (!hasRandom)
                {
                    document.getElementById("missingRandomFromFilename").style.display = "block";
                }
            }
        }, function (message) {
            // I can ignore errors for this AJAX requesy
        }, false
    );
};

// Initialisation
akeeba.Loader.add(['akeeba.System', 'akeeba.Ajax'], function ()
{
    akeeba.System.params.errorCallback = function (error) {};

    akeeba.System.addEventListener("comAkeebaControlPanelProfileSwitch", "change", function ()
    {
        document.forms.switchActiveProfileForm.submit();
    });

    akeeba.System.addEventListener(document.getElementById("btnchangelog"), "click", akeeba.ControlPanel.showChangelog);

    akeeba.System.notification.askPermission();
    akeeba.ControlPanel.checkOutputFolderSecurity();

    var elNotFixedPerms = document.getElementById("notfixedperms");

    if (elNotFixedPerms !== null)
    {
        elNotFixedPerms.parentElement.removeChild(elNotFixedPerms);
    }
});