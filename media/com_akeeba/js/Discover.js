/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Object initialisation
if (typeof akeeba === "undefined")
{
    var akeeba = {};
}

if (typeof akeeba.Discover === "undefined")
{
    akeeba.Discover = {}
}

akeeba.Loader.add(['akeeba.System', 'akeeba.Configuration'], function ()
{
    akeeba.Configuration.URLs["browser"] =
        akeeba.System.getOptions("akeeba.Discover.URLs.browser", "");

    akeeba.System.addEventListener("browserbutton", "click", function ()
    {
        var directory = document.getElementById("directory");

        akeeba.Configuration.onBrowser(directory.value, directory);

        return false;
    });
});