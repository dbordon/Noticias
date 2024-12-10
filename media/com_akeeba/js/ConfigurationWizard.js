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

if (typeof akeeba.Wizard == "undefined")
{
    akeeba.Wizard = {
        URLs:        {},
        execTimes:   [30, 25, 20, 14, 7, 5, 3],
        blockSizes:  [240, 200, 160, 80, 40, 16, 4, 2, 1],
        translation: {}
    }
}

/**
 * Boot up the Configuration Wizard benchmarking process
 */
akeeba.Wizard.boot = function ()
{
    akeeba.Wizard.execTimes  = [30, 25, 20, 14, 7, 5, 3];
    akeeba.Wizard.blockSizes = [480, 400, 240, 200, 160, 80, 40, 16, 4, 2, 1];

    // Show GUI
    document.getElementById("backup-progress-pane").style.display = "block";
    akeeba.Backup.resetTimeoutBar();

    // Before continuing, perform a call to the ping method, so Akeeba Backup knowns that it was configured
    akeeba.System.doAjax(
        {act: "ping"},
        function ()
        {
            akeeba.Wizard.flush();
        },
        function ()
        {
        },
        false,
        10000
    );
};

akeeba.Wizard.flush = function()
{
    akeeba.Backup.resetTimeoutBar();
    akeeba.Backup.startTimeoutBar(30000, 100);

    const stepElement = document.getElementById("step-flush");
    stepElement.className = 'akeeba-label--teal';

    akeeba.System.doAjax(
        {act: 'flush'},
        function(msg) {
            stepElement.className = "akeeba-label--green";

            akeeba.Wizard.minExec();
        },
        function (msg) {
            stepElement.className = "akeeba-label--green";

            akeeba.Wizard.minExec();
        }
    );
}

/**
 * Determine the optimal Minimum Execution Time
 *
 * @param   seconds     How many seconds to test
 * @param   repetition  Which try is this?
 */
akeeba.Wizard.minExec = function (seconds, repetition)
{
    if (seconds == null)
    {
        seconds = 0;
    }
    if (repetition == null)
    {
        repetition = 0;
    }

    akeeba.Backup.resetTimeoutBar();
    akeeba.Backup.startTimeoutBar((2 * seconds + 5) * 1000, 100);

    document.getElementById("backup-substep").textContent =
        akeeba.System.Text._("COM_AKEEBA_CONFWIZ_UI_MINEXECTRY").replace("%s", seconds.toFixed(1));

    var stepElement       = document.getElementById("step-minexec");
    stepElement.className = "akeeba-label--teal";

    akeeba.System.doAjax(
        {act: "minexec", "seconds": seconds},
        function (msg)
        {
            // The ping was successful. Add a repetition count.
            repetition++;
            if (repetition < 3)
            {
                // We need more repetitions
                akeeba.Wizard.minExec(seconds, repetition);
            }
            else
            {
                // Three repetitions reached. Success!
                akeeba.Wizard.minExecApply(seconds);
            }
        },
        function ()
        {
            // We got a failure. Add half a second
            seconds += 0.5;

            if (seconds > 20)
            {
                // Uh-oh... We exceeded our maximum allowance!
                document.getElementById("backup-progress-pane").style.display = "none";
                document.getElementById("error-panel").style.display          = "block";
                document.getElementById("backup-error-message").textContent   =
                    akeeba.System.Text._("COM_AKEEBA_CONFWIZ_UI_CANTDETERMINEMINEXEC");
            }
            else
            {
                akeeba.Wizard.minExec(seconds, 0);
            }
        },
        false,
        (2 * seconds + 5) * 1000
    );
};

/**
 * Applies the AJAX preference and the minimum execution time determined in the previous steps
 *
 * @param   seconds  The minimum execution time, in seconds
 */
akeeba.Wizard.minExecApply = function (seconds)
{
    akeeba.Backup.resetTimeoutBar();
    akeeba.Backup.startTimeoutBar(25000, 100);

    document.getElementById("backup-substep").textContent = akeeba.System.Text._("COM_AKEEBA_CONFWIZ_UI_SAVEMINEXEC");

    akeeba.System.doAjax(
        {act: "applyminexec", "minexec": seconds},
        function (msg)
        {
            var stepElement       = document.getElementById("step-minexec");
            stepElement.className = stepElement.className = "akeeba-label--green";

            akeeba.Wizard.directories();
        },
        function ()
        {
            // Unsuccessful call. Oops!
            document.getElementById("backup-progress-pane").style.display = "none";
            document.getElementById("error-panel").style.display          = "block";
            document.getElementById("backup-error-message").textContent   =
                akeeba.System.Text._("COM_AKEEBA_CONFWIZ_UI_CANTSAVEMINEXEC");
        },
        false
    );
};

/**
 * Automatically determine the optimal output and temporary directories,
 * then make sure they are writable
 */
akeeba.Wizard.directories = function ()
{
    akeeba.Backup.resetTimeoutBar();
    akeeba.Backup.startTimeoutBar(10000, 100);

    document.getElementById("backup-substep").innerHTML = "";

    var stepElement       = document.getElementById("step-directory");
    stepElement.className = "akeeba-label--teal";

    akeeba.System.doAjax(
        {act: "directories"},
        function (msg)
        {
            if (msg)
            {
                var stepElement       = document.getElementById("step-directory");
                stepElement.className = stepElement.className = "akeeba-label--green";

                akeeba.Wizard.database();
            }
            else
            {
                document.getElementById("backup-progress-pane").style.display = "none";
                document.getElementById("error-panel").style.display          = "block";
                document.getElementById("backup-error-message").textContent   =
                    akeeba.System.Text._("COM_AKEEBA_CONFWIZ_UI_CANTFIXDIRECTORIES");
            }
        },
        function ()
        {
            document.getElementById("backup-progress-pane").style.display = "none";
            document.getElementById("error-panel").style.display          = "block";
            document.getElementById("backup-error-message").textContent   =
                akeeba.System.Text._("COM_AKEEBA_CONFWIZ_UI_CANTFIXDIRECTORIES");
        },
        false
    );
};

/**
 * Determine the optimal database dump options, analyzing the site's database
 */
akeeba.Wizard.database = function ()
{
    akeeba.Backup.resetTimeoutBar();
    akeeba.Backup.startTimeoutBar(30000, 50);

    document.getElementById("backup-substep").innerHTML = "";
    var stepElement                                     = document.getElementById("step-dbopt");
    stepElement.className                               = "akeeba-label--teal";

    akeeba.System.doAjax(
        {act: "database"},
        function (msg)
        {
            if (msg)
            {
                var stepElement       = document.getElementById("step-dbopt");
                stepElement.className = stepElement.className = "akeeba-label--green";

                akeeba.Wizard.maxExec();
            }
            else
            {
                document.getElementById("backup-progress-pane").style.display = "none";
                document.getElementById("error-panel").style.display          = "block";
                document.getElementById("backup-error-message").textContent   =
                    akeeba.System.Text._("COM_AKEEBA_CONFWIZ_UI_CANTDBOPT");
            }
        },
        function ()
        {
            document.getElementById("backup-progress-pane").style.display = "none";
            document.getElementById("error-panel").style.display          = "block";
            document.getElementById("backup-error-message").textContent   =
                akeeba.System.Text._("COM_AKEEBA_CONFWIZ_UI_CANTDBOPT");
        },
        false
    );
};

/**
 * Determine the optimal maximum execution time which doesn't cause a timeout or server error
 */
akeeba.Wizard.maxExec = function ()
{
    var exec_time = array_shift(akeeba.Wizard.execTimes);

    if (empty(akeeba.Wizard.execTimes) || (exec_time == null))
    {
        // Darn, we ran out of options
        document.getElementById("backup-progress-pane").style.display = "none";
        document.getElementById("error-panel").style.display          = "block";
        document.getElementById("backup-error-message").textContent   =
            akeeba.System.Text._("COM_AKEEBA_CONFWIZ_UI_EXECTOOLOW");

        return;
    }

    akeeba.Backup.resetTimeoutBar();
    akeeba.Backup.startTimeoutBar((exec_time * 1.2) * 1000, 80);

    var stepElement       = document.getElementById("step-maxexec");
    stepElement.className = "akeeba-label--teal";

    document.getElementById("backup-substep").textContent =
        akeeba.System.Text._("COM_AKEEBA_CONFWIZ_UI_MINEXECTRY").replace("%s", exec_time.toFixed(0));

    akeeba.System.doAjax(
        {act: "maxexec", "seconds": exec_time},
        function (msg)
        {
            if (msg)
            {
                // Success! Save this value.
                akeeba.Wizard.maxExecApply(exec_time);
            }
            else
            {
                // Uh... we have to try something lower than that
                akeeba.Wizard.maxExec();
            }
        },
        function ()
        {
            // Uh... we have to try something lower than that
            akeeba.Wizard.maxExec();
        },
        false,
        38000 // Maximum time to wait: 38 seconds
    );
};

/**
 * Apply the maximum execution time
 *
 * @param   seconds  The number of max execution time (in seconds) we found that works on the server
 */
akeeba.Wizard.maxExecApply = function (seconds)
{
    akeeba.Backup.resetTimeoutBar();
    akeeba.Backup.startTimeoutBar(10000, 100);

    document.getElementById("backup-substep").textContent = akeeba.System.Text._("COM_AKEEBA_CONFWIZ_UI_SAVINGMAXEXEC");

    akeeba.System.doAjax(
        {act: "applymaxexec", "seconds": seconds},
        function ()
        {
            var stepElement       = document.getElementById("step-maxexec");
            stepElement.className = stepElement.className = "akeeba-label--green";

            akeeba.Wizard.partSize();
        },
        function ()
        {
            document.getElementById("backup-progress-pane").style.display = "none";
            document.getElementById("error-panel").style.display          = "block";
            document.getElementById("backup-error-message").textContent   =
                akeeba.System.Text._("COM_AKEEBA_CONFWIZ_UI_CANTSAVEMAXEXEC");
        }
    );
};

/**
 * Try to find the best part size for split archives which works on this server
 */
akeeba.Wizard.partSize = function ()
{
    akeeba.Backup.resetTimeoutBar();

    var block_size = array_shift(akeeba.Wizard.blockSizes);
    if (empty(akeeba.Wizard.blockSizes) || (block_size == null))
    {
        // Uh... I think you are running out of disk space, dude
        document.getElementById("backup-progress-pane").style.display = "none";
        document.getElementById("error-panel").style.display          = "block";
        document.getElementById("backup-error-message").textContent   =
            akeeba.System.Text._("COM_AKEEBA_CONFWIZ_UI_CANTDETERMINEPARTSIZE");

        return;
    }

    var part_size = block_size / 8; // Translate to Mb

    akeeba.Backup.startTimeoutBar(30000, 100);
    document.getElementById("backup-substep").textContent =
        akeeba.System.Text._("COM_AKEEBA_CONFWIZ_UI_PARTSIZE").replace("%s", part_size.toFixed(3));

    var stepElement       = document.getElementById("step-splitsize");
    stepElement.className = "akeeba-label--teal";

    akeeba.System.doAjax(
        {act: "partsize", blocks: block_size},
        function (msg)
        {
            if (msg)
            {
                // We are done
                var stepElement       = document.getElementById("step-splitsize");
                stepElement.className = stepElement.className = "akeeba-label--green";

                akeeba.Wizard.done();
            }
            else
            {
                // Let's try the next (lower) value
                akeeba.Wizard.partSize();
            }
        },
        function (msg)
        {
            // The server blew up on our face. Let's try the next (lower) value.
            akeeba.Wizard.partSize();
        },
        false,
        60000
    );
};

/**
 * The configuration wizard is done
 */
akeeba.Wizard.done = function ()
{
    document.getElementById("backup-progress-pane").style.display = "none";
    document.getElementById("backup-complete").style.display      = "block";
};

akeeba.Loader.add(['akeeba.System', 'akeeba.Ajax', 'akeeba.Backup'], function ()
{
    akeeba.Wizard.boot();
});