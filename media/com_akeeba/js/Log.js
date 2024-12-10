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

if (typeof akeeba.Log === "undefined")
{
    akeeba.Log = {};
}

akeeba.Log.onShowBigLog = function () {
    var iFrameHolder           = document.getElementById("iframe-holder");
    var iFrameSource           = akeeba.System.getOptions("akeeba.Log.iFrameSrc");
    iFrameHolder.style.display = "block";
    iFrameHolder.insertAdjacentHTML("beforeend",
        "<iframe width=\"99%\" src=\"" + iFrameSource + "\" height=\"400px\"/>");
    this.parentNode.style.display = "none";
};

akeeba.Loader.add('akeeba.System', function () {
    akeeba.System.addEventListener("showlog", "click", akeeba.Log.onShowBigLog);
    akeeba.System.addEventListener("comAkeebaLogTagSelector", "change", function () {
        document.forms.adminForm.submit();
    })
});