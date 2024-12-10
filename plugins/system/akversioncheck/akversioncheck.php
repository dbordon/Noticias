<?php
/**
 * @package   akversioncheck
 * @copyright Copyright (c)2006-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Response\JsonResponse;

/**
 * Version Check — Stops Joomla Update lying about whether our software supports Joomla 4.
 *
 * For the record, we were the FIRST third party extensions developer to support Joomla 4 since it was still in Alpha 2
 * back in November 2017 — three and a half years before 4.0 stable was released. We have the easiest upgrade path of
 * any other third party extensions developer: upgrade your site to Joomla 4, install the updates your site finds, done.
 *
 * I had notified the core maitnainers that Joomla Update's idiotic pre-update check was based on a false premise, can
 * not work beyond the simplest use case (“latest version supports the current Joomla 3 and the next Joomla 4 version,
 * whichever these are”), and would simply end up as an automated slander against third party developers (it has caused
 * our sales to drop because people believe Joomla's lies about our PERFECTLY WORKING AND COMPATIBLE software being
 * ‘incompatible’ with Joomla 4).
 *
 * I notified them four times in the year before Joomla 4 was released. They were too stubborn to even put the minimal
 * effort any living creature with more than one working braincell would need to understand the magnitude and importance
 * of the problem.
 *
 * So here we are. I am met with a problem the Joomla project refuses to fix. You know what? It's not my first rodeo.
 *
 * I know how Joomla works. I know **very well** how Joomla's plugin system works. Darn it, I actually wrote the one
 * in Joomla 4, plus the migration and b/c code for concrete events which will be more prominent in Joomla 5. I will use
 * my deep knowledge of Joomla, its plugin system, and my experience to create this plugin which does three things:
 *
 * 1. Adds a notice on the update page about the CORRECT upgrade procedure should it detect you have some incredibly
 *    old, absolutely obsolete extensions of ours.
 *
 * 2. Report the latest available Joomla 3 versions of our extensions as compatible with Joomla 4 (because we constantly
 *    test them with it to make sure nothing breaks on upgrade, thank you very much!).
 *
 * 3. Remove the ugly, misleading, SLANDEROUS notices that our extensions will break your site just because they have
 *    some plugins which are not marked as core Joomla.
 *
 * It does so with elegance and a certain aplomb, if I may say so myself.
 *
 * @since 1.0.0
 */
class plgSystemAkversioncheck extends CMSPlugin
{
	/**
	 * Obsolete extensions.
	 *
	 * If any of these extensions is still installed you will see the message that you need to run MagicEraser.
	 *
	 * @since 1.0.0
	 */
	private const OBSOLETE_EXTENSIONS = [
		// Obsolete extensions
		'com_cmsupdate',
		'plg_system_akgeoip',
		'pkg_yubikey',
		'pkg_yubikey_plugins',
		'plg_system_oneclickaction',
		'pkg_compliance',

		// Libraries and frameworks
		'lib_f0f#prefix',
		'lib_fof30',
		'file_fof30',
		'file_akeebastrapper',
		'file_strapper',
		'files_strapper',
		'file_strapper30',

		// Obsolete extensions formerly bundled with Akeeba Backup
		'amod_akadmin',
		'plg_jmonitoring_akeebabackup',
		'plg_system_akeebaupdatecheck',
		'plg_system_aklazy',
		'plg_system_srp',

		// Obsolete extensions formerly bundled with Admin Tools
		'amod_atjupgrade',
		'plg_quickicon_atoolsjupdatecheck',
		'plg_system_atoolsjupdatecheck',
		'plg_system_atoolsupdatecheck',
		'plg_system_admintoolsactionlog',

		// Obsolete extensions formerly bundled with Akeeba Ticket System
		'plg_ats_alphauserpoints',
		'plg_ats_akeebasubs',
		'plg_ats_akeebasubslegacy',

		// Obsolete extensions formerly bundled with DocImport
		'plg_sh404sefextplugins_com_docimport',
		'mod_docimport_search',

		// Obsolete extensions formerly bundled with Akeeba Release System
		'plg_ars_bleedingedgediff',
		'plg_ars_bleedingedgematurity',
		'plg_ars_tainting',
		'plg_sh404sefextplugins_com_ars',
		'file_ars',
		'files_ars',
		'mod_arsdlid',
		'plg_system_arsjed',

		// Obsolete extensions formerly bundled with Akeeba Subscriptions
		'amod_akeebasubs',
		'mod_aktaxcountry',
		'plg_akeebasubs_aceshop',
		'plg_akeebasubs_acymailing',
		'plg_akeebasubs_adminemails',
		'plg_akeebasubs_affemails',
		'plg_akeebasubs_ageverification',
		'plg_akeebasubs_agora',
		'plg_akeebasubs_agreetoeu',
		'plg_akeebasubs_agreetotos',
		'plg_akeebasubs_atscreditslegacy',
		'plg_akeebasubs_autocity',
		'plg_akeebasubs_canalyticscommerce',
		'plg_akeebasubs_cb',
		'plg_akeebasubs_cbsync',
		'plg_akeebasubs_ccinvoices',
		'plg_akeebasubs_communityacl',
		'plg_akeebasubs_constantcontact',
		'plg_akeebasubs_customfields',
		'plg_akeebasubs_docman',
		'plg_akeebasubs_easydiscuss',
		'plg_akeebasubs_freshbooks',
		'plg_akeebasubs_frontenduseraccess',
		'plg_akeebasubs_gacommerce',
		'plg_akeebasubs_invoices',
		'plg_akeebasubs_iplogger',
		'plg_akeebasubs_iproperty',
		'plg_akeebasubs_jce',
		'plg_akeebasubs_jomsocial',
		'plg_akeebasubs_joomlaprofilesync',
		'plg_akeebasubs_juga',
		'plg_akeebasubs_jxjomsocial',
		'plg_akeebasubs_k2',
		'plg_akeebasubs_kunena',
		'plg_akeebasubs_mailchimp',
		'plg_akeebasubs_mijoshop',
		'plg_akeebasubs_needslogout',
		'plg_akeebasubs_ninjaboard',
		'plg_akeebasubs_phocadownload',
		'plg_akeebasubs_projectfork',
		'plg_akeebasubs_projectfork4',
		'plg_akeebasubs_recaptcha',
		'plg_akeebasubs_redshop',
		'plg_akeebasubs_redshopusersync',
		'plg_akeebasubs_reseller',
		'plg_akeebasubs_samplefields',
		'plg_akeebasubs_slavesubs',
		'plg_akeebasubs_sql',
		'plg_akeebasubs_subscriptionemailsdebug',
		'plg_akeebasubs_tienda',
		'plg_akeebasubs_tracktime',
		'plg_akeebasubs_userdelete',
		'plg_akeebasubs_vm',
		'plg_akeebasubs_vm2',
		'plg_akeebasubs_zohoinvoice',
		'plg_akpayment_2checkout',
		'plg_akpayment_2conew',
		'plg_akpayment_allopass',
		'plg_akpayment_alphauserpoints',
		'plg_akpayment_authorizenet',
		'plg_akpayment_be2bill',
		'plg_akpayment_beanstream',
		'plg_akpayment_braintree',
		'plg_akpayment_cardstream',
		'plg_akpayment_cashu',
		'plg_akpayment_ccavenue',
		'plg_akpayment_clickandbuy',
		'plg_akpayment_cmcic',
		'plg_akpayment_deltapay',
		'plg_akpayment_dwolla',
		'plg_akpayment_epaydk',
		'plg_akpayment_eselectplus',
		'plg_akpayment_eway',
		'plg_akpayment_ewayrapid3',
		'plg_akpayment_exact',
		'plg_akpayment_gocardless',
		'plg_akpayment_googlecheckout',
		'plg_akpayment_ifthen',
		'plg_akpayment_mercadopago',
		'plg_akpayment_mobilpaycc',
		'plg_akpayment_mobilpaysms',
		'plg_akpayment_moip',
		'plg_akpayment_moipassinaturas',
		'plg_akpayment_moneris',
		'plg_akpayment_nochex',
		'plg_akpayment_none',
		'plg_akpayment_offline',
		'plg_akpayment_pagseguro',
		'plg_akpayment_payfast',
		'plg_akpayment_paymill',
		'plg_akpayment_paymilldss3',
		'plg_akpayment_paypal',
		'plg_akpayment_paypalpaymentspro',
		'plg_akpayment_paypalproexpress',
		'plg_akpayment_paypoint',
		'plg_akpayment_paysafe',
		'plg_akpayment_payu',
		'plg_akpayment_postfinancech',
		'plg_akpayment_przelewy24',
		'plg_akpayment_rbkmoney',
		'plg_akpayment_realex',
		'plg_akpayment_robokassa',
		'plg_akpayment_saferpay',
		'plg_akpayment_sagepay',
		'plg_akpayment_scnet',
		'plg_akpayment_scnetintegrated',
		'plg_akpayment_skrill',
		'plg_akpayment_stripe',
		'plg_akpayment_suomenverkkomaksut',
		'plg_akpayment_upay',
		'plg_akpayment_verotel',
		'plg_akpayment_viva',
		'plg_akpayment_wepay',
		'plg_akpayment_worldpay',
		'plg_akpayment_zarinpal',
		'plg_ccinvoicetags_akeebasubs',
		'plg_sh404sefextplugins_com_akeebasubs',
		'plg_system_as2cocollation',
		'plg_system_affiliatesessiongeneration',
		'plg_system_aslogoutuser',
		'plg_system_aspaypalcollation',
		'plg_system_idevaffiliate',
		'plg_system_postaffiliatepro',
		'plg_user_aslogoutuser',
		'plg_user_asresetform',

		// Obsolete extensions formerly bundled with Akeeba YubiKey Authentication Plugins
		'plg_user_yubikey',
		'plg_authentication_yubikey',
		'plg_twofactorauth_yubikeytotp',
		'plg_twofactorauth_yubikeyplus',
		'plg_twofactorauth_u2f',

		// Obsolete extensions formerly bundled with Akeeba CMS Update
		'plg_system_cmsupdateemail',
		'plg_quickicon_cmsupdate',
	];

	/**
	 * Allowed extensions.
	 *
	 * These extensions will be marked as compatible with Joomla 4 even though they technically have no release for
	 * Joomla 4, or cannot be installed on Joomla 4.1 and later. These are extensions **KNOWN** to be safe, which can
	 * be removed after the upgrade to Joomla 4.
	 *
	 * @since 1.0.0
	 */
	private const ALLOWED_EXTENSIONS = [
		// ### Libraries and frameworks. They remain inactive in Joomla 4.
		'lib_f0f#prefix',
		'lib_fof',
		'lib_fof30',
		'file_fof30',
		'file_fof40',
		'file_fef',
		'file_akeebastrapper',
		'file_strapper',
		'files_strapper',
		'file_strapper30',

		// Obsolete extensions formerly bundled with Akeeba Backup
		'plg_jmonitoring_akeebabackup',

		// Obsolete extensions formerly bundled with Admin Tools
		'plg_system_admintoolsactionlog',

		// Obsolete extensions formerly bundled with Akeeba Ticket System
		'plg_ats_alphauserpoints',
		'plg_ats_akeebasubs',
		'plg_ats_akeebasubslegacy',

		// Obsolete extensions formerly bundled with DocImport
		'plg_sh404sefextplugins_com_docimport',

		// Obsolete extensions formerly bundled with Akeeba Release System
		'plg_ars_bleedingedgediff',
		'plg_ars_bleedingedgematurity',
		'plg_ars_tainting',
		'plg_sh404sefextplugins_com_ars',
		'file_ars',
		'files_ars',
		'mod_arsdlid',
		'plg_system_arsjed',

		// Obsolete extensions formerly bundled with Akeeba Subscriptions
		'amod_akeebasubs',
		'mod_aktaxcountry',
		'plg_akeebasubs_aceshop',
		'plg_akeebasubs_acymailing',
		'plg_akeebasubs_adminemails',
		'plg_akeebasubs_affemails',
		'plg_akeebasubs_ageverification',
		'plg_akeebasubs_agora',
		'plg_akeebasubs_agreetoeu',
		'plg_akeebasubs_agreetotos',
		'plg_akeebasubs_atscreditslegacy',
		'plg_akeebasubs_autocity',
		'plg_akeebasubs_canalyticscommerce',
		'plg_akeebasubs_cb',
		'plg_akeebasubs_cbsync',
		'plg_akeebasubs_ccinvoices',
		'plg_akeebasubs_communityacl',
		'plg_akeebasubs_constantcontact',
		'plg_akeebasubs_customfields',
		'plg_akeebasubs_docman',
		'plg_akeebasubs_easydiscuss',
		'plg_akeebasubs_freshbooks',
		'plg_akeebasubs_frontenduseraccess',
		'plg_akeebasubs_gacommerce',
		'plg_akeebasubs_invoices',
		'plg_akeebasubs_iplogger',
		'plg_akeebasubs_iproperty',
		'plg_akeebasubs_jce',
		'plg_akeebasubs_jomsocial',
		'plg_akeebasubs_joomlaprofilesync',
		'plg_akeebasubs_juga',
		'plg_akeebasubs_jxjomsocial',
		'plg_akeebasubs_k2',
		'plg_akeebasubs_kunena',
		'plg_akeebasubs_mailchimp',
		'plg_akeebasubs_mijoshop',
		'plg_akeebasubs_needslogout',
		'plg_akeebasubs_ninjaboard',
		'plg_akeebasubs_phocadownload',
		'plg_akeebasubs_projectfork',
		'plg_akeebasubs_projectfork4',
		'plg_akeebasubs_recaptcha',
		'plg_akeebasubs_redshop',
		'plg_akeebasubs_redshopusersync',
		'plg_akeebasubs_reseller',
		'plg_akeebasubs_samplefields',
		'plg_akeebasubs_slavesubs',
		'plg_akeebasubs_sql',
		'plg_akeebasubs_subscriptionemailsdebug',
		'plg_akeebasubs_tienda',
		'plg_akeebasubs_tracktime',
		'plg_akeebasubs_userdelete',
		'plg_akeebasubs_vm',
		'plg_akeebasubs_vm2',
		'plg_akeebasubs_zohoinvoice',
		'plg_akpayment_2checkout',
		'plg_akpayment_2conew',
		'plg_akpayment_allopass',
		'plg_akpayment_alphauserpoints',
		'plg_akpayment_authorizenet',
		'plg_akpayment_be2bill',
		'plg_akpayment_beanstream',
		'plg_akpayment_braintree',
		'plg_akpayment_cardstream',
		'plg_akpayment_cashu',
		'plg_akpayment_ccavenue',
		'plg_akpayment_clickandbuy',
		'plg_akpayment_cmcic',
		'plg_akpayment_deltapay',
		'plg_akpayment_dwolla',
		'plg_akpayment_epaydk',
		'plg_akpayment_eselectplus',
		'plg_akpayment_eway',
		'plg_akpayment_ewayrapid3',
		'plg_akpayment_exact',
		'plg_akpayment_gocardless',
		'plg_akpayment_googlecheckout',
		'plg_akpayment_ifthen',
		'plg_akpayment_mercadopago',
		'plg_akpayment_mobilpaycc',
		'plg_akpayment_mobilpaysms',
		'plg_akpayment_moip',
		'plg_akpayment_moipassinaturas',
		'plg_akpayment_moneris',
		'plg_akpayment_nochex',
		'plg_akpayment_none',
		'plg_akpayment_offline',
		'plg_akpayment_pagseguro',
		'plg_akpayment_payfast',
		'plg_akpayment_paymill',
		'plg_akpayment_paymilldss3',
		'plg_akpayment_paypal',
		'plg_akpayment_paypalpaymentspro',
		'plg_akpayment_paypalproexpress',
		'plg_akpayment_paypoint',
		'plg_akpayment_paysafe',
		'plg_akpayment_payu',
		'plg_akpayment_postfinancech',
		'plg_akpayment_przelewy24',
		'plg_akpayment_rbkmoney',
		'plg_akpayment_realex',
		'plg_akpayment_robokassa',
		'plg_akpayment_saferpay',
		'plg_akpayment_sagepay',
		'plg_akpayment_scnet',
		'plg_akpayment_scnetintegrated',
		'plg_akpayment_skrill',
		'plg_akpayment_stripe',
		'plg_akpayment_suomenverkkomaksut',
		'plg_akpayment_upay',
		'plg_akpayment_verotel',
		'plg_akpayment_viva',
		'plg_akpayment_wepay',
		'plg_akpayment_worldpay',
		'plg_akpayment_zarinpal',
		'plg_ccinvoicetags_akeebasubs',
		'plg_sh404sefextplugins_com_akeebasubs',
		'plg_system_as2cocollation',
		'plg_system_affiliatesessiongeneration',
		'plg_system_aslogoutuser',
		'plg_system_aspaypalcollation',
		'plg_system_idevaffiliate',
		'plg_system_postaffiliatepro',
		'plg_user_aslogoutuser',
		'plg_user_asresetform',

		// Obsolete extensions formerly bundled with Akeeba CMS Update
		'plg_system_cmsupdateemail',
		'plg_quickicon_cmsupdate',

		// Packages: Akeeba Backup
		'pkg_akeeba',
		'com_akeeba',
		'file_akeeba',
		'plg_actionlog_akeebabackup',
		'plg_console_akeebabackup',
		'plg_installer_akeebabackup',
		'plg_quickicon_akeebabackup',
		'plg_system_akversioncheck',
		'plg_system_backuponupdate',

		// Packages: Admin Tools
		'pkg_admintools',
		'com_admintools',
		'file_admintools',
		'plg_actionlog_admintools',
		'plg_installer_admintools',
		'plg_system_admintools',

		// Packages: Akeeba Ticket System
		'pkg_ats',
		'com_ats',
		'file_ats',
		'amod_atsstats',
		'mod_atscredits',
		'mod_atstickets',
		'plg_actionlog_ats',
		'plg_ats_akeebasubs',
		'plg_ats_autoclose',
		'plg_ats_autoreply',
		'plg_ats_customfields',
		'plg_ats_deletenotes',
		'plg_ats_easyavatar',
		'plg_ats_geolocation',
		'plg_ats_gravatar',
		'plg_ats_mailfetch',
		'plg_ats_postemail',
		'plg_ats_removeattachments',
		'plg_ats_sociallike',
		'plg_ats_usergroups',
		'plg_atsinstantreply_docimport',
		'plg_atsinstantreply_tickets',
		'plg_content_atscredits',
		'plg_editors-xtd_atscannedreplies',
		'plg_finder_ats',
		'plg_installer_ats',
		'plg_search_ats',
		'plg_sh404sefextplugins_ats',
		'plg_user_ats',

		// Packages: Akeeba Subscriptions
		'pkg_akeebasubs',
		'com_akeebasubs',
		'file_akeebasubs',
		'mod_akmysubs',
		'mod_aksexpires',
		'mod_akslevels',
		'mod_aksubslist',
		'plg_content_astimedrelease',
		'plg_content_asprice',
		'plg_content_asrestricted',
		'plg_content_aslink',
		'plg_system_asexpirationcontrol',
		'plg_system_asuserregredir',
		'plg_system_asexpirationnotify',
		'plg_system_asfixrenewalsflag',
		'plg_akeebasubs_atscredits',
		'plg_akeebasubs_subscriptionemails',
		'plg_akeebasubs_contentpublish',
		'plg_akeebasubs_joomla',

		// Packages: Akeeba Release System
		'pkg_ars',
		'com_ars',
		'file_ars',
		'mod_arsdlid',
		'mod_arsdownloads',
		'plg_content_arsdlid',
		'plg_content_arslatest',
		'plg_system_arsjed',
		'plg_editors-xtd_arslink',

		// Packages: Version Compatibility
		'pkg_compatibility',
		'com_compatibility',

		// Packages: Akeeba DataCompliance
		'pkg_datacompliance',
		'pkg_compliance',
		'com_datacompliance',
		'file_datacompliance',
		'plg_user_datacompliance',
		'plg_datacompliance_s3',
		'plg_datacompliance_ars',
		'plg_datacompliance_loginguard',
		'plg_datacompliance_akeebasubs',
		'plg_datacompliance_ats',
		'plg_datacompliance_joomla',
		'plg_datacompliance_email',
		'plg_system_datacompliancecookie',
		'plg_system_datacompliance',

		// Packages: Akeeba ContactUs
		'pkg_contactus',
		'com_contactus',
		'file_contactus',

		// Packages: Akeeba DocImport
		'pkg_docimport',
		'com_docimport',
		'file_docimport',
		'mod_docimport_categories',
		'mod_docimport_search',
		'plg_search_docimport',
		'plg_finder_docimport',

		// Packages: Akeeba Engage
		'pkg_engage',
		'com_engage',
		'file_engage',
		'plg_privacy_engage',
		'plg_content_engage',
		'plg_user_engage',
		'plg_datacompliance_engage',
		'plg_engage_akismet',
		'plg_engage_gravatar',
		'plg_engage_email',
		'plg_system_engagecache',
		'plg_actionlog_engage',

		// Packages: Akeeba LoginGuard
		'pkg_loginguard',
		'com_loginguard',
		'file_loginguard',
		'plg_user_loginguard',
		'plg_system_loginguard',
		'plg_actionlog_loginguard',
		'plg_loginguard_smsapi',
		'plg_loginguard_yubikey',
		'plg_loginguard_fixed',
		'plg_loginguard_webauthn',
		'plg_loginguard_pushbullet',
		'plg_loginguard_totp',
		'plg_loginguard_email',
		'plg_loginguard_u2f',

		// Packages: Akeeba SocialLogin
		'plg_sociallogin_apple',
		'plg_sociallogin_discord',
		'plg_sociallogin_google',
		'plg_sociallogin_microsoft',
		'plg_sociallogin_github',
		'plg_sociallogin_facebook',
		'plg_sociallogin_linkedin',
		'plg_sociallogin_twitter',
		'plg_system_sociallogin',

		// Dark Magic
		'plg_system_darkmagic',

		// Internal
		'tpl_akeeba',
		'plg_user_foftoken',
		'plg_system_dateshift',
		'plg_system_mailmagic',
		'amod_emailsetup',
		'pkg_passwordless',
		'plg_system_passwordless',
		'plg_content_fieldsorter',
		'plg_system_usertype',
		'amod_userstats',
		'plg_system_socialmagick',
		'plg_system_expose',
		'plg_system_ampcontent',
		'plg_system_combinator',
		'plg_system_bootify',
	];

	/**
	 * @var   AdministratorApplication
	 * @since 1.0.0
	 */
	protected $app;

	/**
	 * @var   JDatabaseDriver
	 * @since 1.0.0
	 */
	protected $dbo;

	/**
	 * Map extension names to extension IDs
	 *
	 * @var   array
	 * @since 1.0.0
	 */
	private $extensionIds = [];

	/**
	 * The IDs of self::ALLOWED_EXTENSIONS installed on the site.
	 *
	 * @var   int[]
	 * @since 1.0.0
	 */
	private $allowedExtensionIds = [];

	public function onAfterInitialise()
	{
		// This only applies on Joomla 3.10
		if (
			version_compare(JVERSION, '3.10.0', 'lt')
			|| version_compare(JVERSION, '3.10.999999', 'gt')
		)
		{
			return;
		}

		// Make sure this is the back-end
		try
		{
			$app = Factory::getApplication();
		}
		catch (Exception $e)
		{
			return;
		}

		if (!$app->isClient('administrator'))
		{
			return;
		}

		// Make sure a user is logged in
		$user = JFactory::getUser();

		if (!is_object($user) || $user->guest)
		{
			return;
		}

		// Make sure the user is a Super User or otherwise allowed to upgrade the site
		if (!$user->authorise('core.admin', 'com_joomlaupdate'))
		{
			return;
		}

		$component = $this->app->input->getCmd('option');
		$task      = $this->app->input->getCmd('task');

		if ($component == 'com_plugins')
		{
			$this->onDirectUnpublish($task);

			$this->onApplyOrSave($task);

			return;
		}

		if ($component !== 'com_joomlaupdate')
		{
			return;
		}

		if ($task === null)
		{
			$this->conditionallyShowMessage();
		}
		elseif ($task === 'update.fetchextensioncompatibility')
		{
			$this->populateAllowedExtensionIDs();

			$this->manipulateJoomlaUpdate();
		}
	}

	public function onBeforeRender()
	{
		// This only applies on Joomla 3.10
		if (
			version_compare(JVERSION, '3.10.0', 'lt')
			|| version_compare(JVERSION, '3.10.999999', 'gt')
		)
		{
			return;
		}

		// Make sure this is the back-end
		try
		{
			$app = Factory::getApplication();
		}
		catch (Exception $e)
		{
			return;
		}

		if (!$app->isClient('administrator'))
		{
			return;
		}

		// Make sure a user is logged in
		$user = JFactory::getUser();

		if (!is_object($user) || $user->guest)
		{
			return;
		}

		// Make sure the user is a Super User or otherwise allowed to upgrade the site
		if (!$user->authorise('core.admin', 'com_joomlaupdate'))
		{
			return;
		}

		$component = $this->app->input->getCmd('option');

		if ($component !== 'com_joomlaupdate')
		{
			return;
		}

		$doc = $this->app->getDocument();

		if (empty($doc))
		{
			return;
		}

		$nonCoreCriticalPlugins = $doc->getScriptOptions('nonCoreCriticalPlugins');

		if (empty($nonCoreCriticalPlugins))
		{
			return;
		}

		$this->populateAllowedExtensionIDs();

		$nonCoreCriticalPlugins = array_filter(
			$nonCoreCriticalPlugins,
			function (object $entry)
			{
				return !in_array($entry->extension_id, $this->allowedExtensionIds);
			}
		);

		$doc->addScriptOptions('nonCoreCriticalPlugins', $nonCoreCriticalPlugins, false);
	}

	private function onDirectUnpublish($task)
	{
		$allowedTasks = ['unpublish', 'plugins.unpublish'];

		if (!in_array($task, $allowedTasks))
		{
			return;
		}

		// Get a list of all IDs in the request
		$ids   = $this->app->input->get('cid', [], 'array');
		$ids[] = $this->app->input->getInt('id', null);

		// Get the plugin ID for System - Admin Tools
		$ourId = $this->getExtensionId('plg_system_akversioncheck');

		if (empty($ourId))
		{
			return;
		}

		// Does the ID exist in the array? We need to be thorough, we can't do a simple in_array.
		foreach ($ids as $id)
		{
			$id = (int) trim($id);

			if ($id == $ourId)
			{
				throw new RuntimeException(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'), 403);
			}
		}
	}

	private function onApplyOrSave($task)
	{
		$allowedTasks = ['apply', 'save', 'plugins.apply', 'plugins.save', 'plugin.apply', 'plugin.save'];

		if (!in_array($task, $allowedTasks))
		{
			return;
		}

		// Get a list of all IDs in the request
		$ids   = $this->app->input->get('cid', [], 'array');
		$ids[] = $this->app->input->getInt('id', null);
		$ids[] = $this->app->input->getInt('extension_id', null);

		// Get the plugin ID for System - Admin Tools
		$ourId = $this->getExtensionId('plg_system_akversioncheck');

		if (empty($ourId))
		{
			return;
		}

		// Does the ID exist in the array? We need to be thorough, we can't do a simple in_array.
		$found = false;

		foreach ($ids as $id)
		{
			$id = (int) trim($id);

			if ($id == $ourId)
			{
				$found = true;

				break;
			}
		}

		if (!$found)
		{
			return;
		}

		// Get the form data and look for the enabled field
		$jform = $this->app->input->get('jform', [], 'array');

		if (!isset($jform['enabled']))
		{
			// Not saving the "enabled" value
			return;
		}

		if ($jform['enabled'] == 1)
		{
			// The plugin is being activated
			return;
		}

		// Apparently someone tries to deactivate the plugin. NOPE.
		throw new RuntimeException(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'), 403);
	}


	private function manipulateJoomlaUpdate()
	{
		$id      = (int) $_GET['extension-id'];
		$version = $this->app->input->get('extension-version');

		// Must be one of our allowed extensions
		if (empty($id) || !in_array($id, $this->allowedExtensionIds))
		{
			return;
		}

		// Check for updates to the extension itself
		if (!class_exists(JoomlaupdateModelDefault::class))
		{
			require_once JPATH_ADMINISTRATOR . '/components/com_joomlaupdate/models/default.php';
		}

		/** @var JoomlaupdateModelDefault $model */
		$model = Joomla\CMS\MVC\Model\BaseDatabaseModel::getInstance('Default', 'JoomlaupdateModel');

		$currentCompatibilityStatus = $model->fetchCompatibility($id, JVERSION);
		$currentUpdateVersion       = false;
		$resultGroup                = 3;

		if ($currentCompatibilityStatus->state == 1 && !empty($currentCompatibilityStatus->compatibleVersions))
		{
			$resultGroup          = 2;
			$currentUpdateVersion = end($currentCompatibilityStatus->compatibleVersions);
		}

		// Construct the response
		$response = [
			'upgradeCompatibilityStatus' => [
				'state'             => 1,
				'compatibleVersion' => $currentUpdateVersion ?: $version,
			],
			'currentCompatibilityStatus' => [
				'state'             => 1,
				'compatibleVersion' => $currentUpdateVersion ?: $version,
			],
			'resultGroup'                => $resultGroup,
			'upgradeWarning'             => 0,
		];

		// Send the response
		$this->app->mimeType = 'application/json';
		$this->app->charSet  = 'utf-8';
		$this->app->setHeader('Content-Type', $this->app->mimeType . '; charset=' . $this->app->charSet);
		$this->app->sendHeaders();

		try
		{
			echo new JsonResponse($response);
		}
		catch (Exception $e)
		{
			echo $e;
		}

		$this->app->close();
	}

	private function populateAllowedExtensionIDs()
	{
		$this->allowedExtensionIds = array_filter(
			array_map(
				function (string $extension): ?int {
					return $this->getExtensionId($extension);
				},
				self::ALLOWED_EXTENSIONS
			)
		);
	}

	private function conditionallyShowMessage()
	{
		if (!class_exists(JoomlaupdateModelDefault::class))
		{
			require_once JPATH_ADMINISTRATOR . '/components/com_joomlaupdate/models/default.php';
		}
		// We must have an update
		/** @var JoomlaupdateModelDefault $model */
		$model      = Joomla\CMS\MVC\Model\BaseDatabaseModel::getInstance('Default', 'JoomlaupdateModel');
		$updateInfo = $model->getUpdateInformation();

		if (!$updateInfo['hasUpdate'])
		{
			return;
		}

		// The new version must be in the 4.x range
		if (version_compare($updateInfo['latest'], '4.0.0', 'lt'))
		{
			return;
		}

		// We must have obsolete extensions
		$hasAkeebaSubscriptions = $this->hasAkeebaSubscriptions();
		$hasObsoleteExtensions  = $this->hasObsoleteExtensions();

		if ($hasObsoleteExtensions || $hasAkeebaSubscriptions)
		{
			$this->loadLanguage();

			$message =
				'<h3>' .
				Text::_('PLG_SYSTEM_AKVERSIONCHECK_LBL_TITLE') .
				'</h3>' .
				'<p>' .
				Text::_('PLG_SYSTEM_AKVERSIONCHECK_LBL_CONTENT')
				. '</p>';

			if ($hasObsoleteExtensions)
			{
				$message .= '<p>' . Text::sprintf(
						$hasAkeebaSubscriptions ? 'PLG_SYSTEM_AKVERSIONCHECK_LBL_MAGICERASER_WITH_SUBS' : 'PLG_SYSTEM_AKVERSIONCHECK_LBL_MAGICERASER',
						'https://github.com/akeeba/magiceraser/releases/latest'
					);
			}

			if ($hasAkeebaSubscriptions)
			{
				$message .= '<p>' .
					Text::_('PLG_SYSTEM_AKVERSIONCHECK_LBL_AKEEBASUBSCRIPTIONS') .
					'</p>';
			}

			$message .= '<hr/>';

			$this->app->enqueueMessage($message, 'error');
		}
	}

	private function hasObsoleteExtensions(): bool
	{
		return array_reduce(
			self::OBSOLETE_EXTENSIONS,
			function (bool $carry, string $extension): bool {
				return $carry || !empty($this->getExtensionId($extension));
			},
			false
		);
	}

	private function hasAkeebaSubscriptions()
	{
		return !empty($this->getExtensionId('pkg_akeebasubs'))
			|| !empty($this->getExtensionId('com_akeebasubs'));
	}

	private function getExtensionId(string $extension): ?int
	{
		if (isset($this->extensionIds[$extension]))
		{
			return $this->extensionIds[$extension];
		}

		$this->extensionIds[$extension] = null;

		$criteria = $this->extensionNameToCriteria($extension);

		if (empty($criteria))
		{
			return $this->extensionIds[$extension];
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('extension_id'))
			->from($db->quoteName('#__extensions'));

		foreach ($criteria as $key => $value)
		{
			$query->where($db->qn($key) . ' = ' . $db->quote($value));
		}

		try
		{
			$this->extensionIds[$extension] = (int) $db->setQuery($query)->loadResult();
		}
		catch (RuntimeException $e)
		{
			return null;
		}

		return $this->extensionIds[$extension];
	}

	private function extensionNameToCriteria(string $extensionName): array
	{
		$parts = explode('_', $extensionName, 3);

		switch ($parts[0])
		{
			case 'pkg':
				return [
					'type'    => 'package',
					'element' => $extensionName,
				];

			case 'com':
				return [
					'type'    => 'component',
					'element' => $extensionName,
				];

			case 'plg':
				return [
					'type'    => 'plugin',
					'folder'  => $parts[1],
					'element' => $parts[2],
				];

			case 'mod':
				return [
					'type'      => 'module',
					'element'   => $extensionName,
					'client_id' => 0,
				];

			// That's how we note admin modules
			case 'amod':
				return [
					'type'      => 'module',
					'element'   => substr($extensionName, 1),
					'client_id' => 1,
				];

			case 'file':
			case 'files':
				return [
					'type'    => 'file',
					'element' => $extensionName,
				];

			case 'lib':
				$element = substr($extensionName, 4);

				if (substr($element, -7) === '#prefix')
				{
					$element = 'lib_' . substr($element, 0, -7);
				}

				return [
					'type'    => 'library',
					'element' => $element,
				];

			case 'tpl':
				return [
					'type'    => 'template',
					'element' => substr($extensionName, 4),
				];
		}

		return [];
	}
}
