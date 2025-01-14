<?php
/**
 * @package    JLSitemap Component
 * @version    2.0.0
 * @author     Joomline - joomline.ru
 * @copyright  Copyright (c) 2010 - 2022 Joomline. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://joomline.ru/
 */

namespace Joomla\Component\JLSitemap\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\JLSitemap\Administrator\Helper\SecretsHelper;
use Joomla\Registry\Registry;

class SitemapController extends BaseController
{
    /**
     * Method to generate sitemap.
     *
     * @return  bool True on success, False on failure.
     *
     * @throws  \Exception
     *
     * @since  1.4.1
     */
    public function generate()
    {
        $app    = Factory::getApplication();
        $cookie = 'jlsitemap_generation';
        $result = $this->input->cookie->get($cookie, false, 'raw');
        $debug  = (!empty($this->input->get('debug', '')));

        // Redirect to site controller
        if (!$result || $debug) {
            // Prepare redirect
            $redirect = [
                'option'     => 'com_jlsitemap',
                'task'       => 'sitemap.generate',
                'access_key' => $this->getAccessKey(),
                'messages'   => 0,
                'cookies'    => ($debug) ? 0 : 1,
                'redirect'   => ($debug) ? 0 : 1
            ];
            if ($debug) {
                $redirect['debug'] = 1;
            } else {
                $redirect['return'] = base64_encode(Route::_('index.php?option=com_jlsitemap&task=sitemap.generate'));
            }

            $app->redirect(trim(Uri::root(true), '/') . '/index.php?' . http_build_query($redirect));

            return true;
        }

        // Get Response
        $response = new Registry($result);
        $message  = $response->get('message');
        $data     = new Registry($response->get('data'));
        $all      = $data->get('all', 0);
        $includes = $data->get('includes', 0);
        $excludes = $data->get('excludes', 0);

        // Remove cookie
        $this->input->cookie->set(
            $cookie,
            '',
            Factory::getDate('-1 day')->toUnix(),
            $app->get('cookie_path', '/'),
            $app->get('cookie_domain'),
            $app->isSSLConnection()
        );

        // Set error
        if (!$response->get('success')) {
            $this->setError($message);
            $this->setMessage($message, 'error');
            $this->setRedirect('index.php?option=com_jlsitemap');

            return false;
        }

        // Set success
        $app->enqueueMessage(Text::sprintf('COM_JLSITEMAP_SITEMAP_GENERATION_SUCCESS', $all));
        $app->enqueueMessage(
            Text::sprintf('COM_JLSITEMAP_SITEMAP_GENERATION_SUCCESS_EXCLUDES', $excludes),
            'warning'
        );
        $app->enqueueMessage(
            Text::sprintf('COM_JLSITEMAP_SITEMAP_GENERATION_SUCCESS_INCLUDES', $includes),
            'notice'
        );

        $this->setRedirect('index.php?option=com_jlsitemap');

        return true;
    }

    /**
     * Method to get component access key.
     *
     * @return  string Access key.
     *
     * @since  1.4.1
     */
    protected function getAccessKey()
    {
        return SecretsHelper::getAccessKey();
    }

    /**
     * Method to delete sitemap.
     *
     * @return  bool True on success, False on failure.
     *
     * @throws  \Exception
     *
     * @since  1.4.1
     */
    public function delete()
    {
        $app    = Factory::getApplication();
        $cookie = 'jlsitemap_delete';
        $result = $this->input->cookie->get($cookie, false, 'raw');

        // Redirect to site controller
        if (!$result) {
            // Prepare redirect
            $redirect = array(
                'option'     => 'com_jlsitemap',
                'task'       => 'sitemap.delete',
                'access_key' => $this->getAccessKey(),
                'messages'   => 0,
                'cookies'    => 1,
                'redirect'   => 1,
                'return'     => base64_encode(Route::_('index.php?option=com_jlsitemap&task=sitemap.delete'))
            );

            $app->redirect(trim(Uri::root(true), '/') . '/index.php?' . http_build_query($redirect));

            return true;
        }

        // Get Response
        $response = new Registry($result);
        $message  = ($response->get('success')) ? Text::_('COM_JLSITEMAP_SITEMAP_DELETE_SUCCESS') :
            Text::_('COM_JLSITEMAP_SITEMAP_DELETE_FAILURE');

        // Remove cookie
        $this->input->cookie->set(
            $cookie,
            '',
            Factory::getDate('-1 day')->toUnix(),
            $app->get('cookie_path', '/'),
            $app->get('cookie_domain'),
            $app->isSSLConnection()
        );

        // Set error
        if (!$response->get('success')) {
            $this->setError($message);
            $this->setMessage($message, 'error');
            $this->setRedirect('index.php?option=com_jlsitemap');

            return false;
        }

        // Set success
        $this->setMessage($message);
        $this->setRedirect('index.php?option=com_jlsitemap');

        return true;
    }
}