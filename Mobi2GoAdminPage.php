<?php
class Mobi2GoAdminPage
{
    private $options;
    private $api_error = false;

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_page'));
        add_action('admin_init', array($this, 'page_init'));
        add_action('admin_head', array($this, 'register_head'));
    }

    public function add_page()
    {
        add_menu_page(
            'Mobi2Go',
            'Mobi2Go',
            'administrator',
            'mobi2go',
            array($this, 'page'),
            'data:image/svg+xml;base64,PHN2ZyB2aWV3Qm94PSIwIDAgMzEuMyAzMiIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBmaWxsPSJjdXJyZW50Q29sb3IiIGQ9Im0xMy4xIDIyLjktNy4zIDguNWMtMS41IDEuNy00LjItLjUtMi44LTIuNGw3LjYtOC44Yy4xIDAgLjUtLjEuNiAwbC03LjcgOC45Yy0uNS41LS41IDEuMS0uMiAxLjUuMS4yLjIuMy4zLjUuMS4xLjMuMi41LjMuNS4xIDEgMCAxLjUtLjVsNy41LTguOGMwIC4yLjEuNiAwIC44em0zLTQuNiA5LjYgMTAuOWMxIDEuMS0uNiAyLjYtMS42IDEuNGwtOS4yLTEwLjh6bS4yIDQuNiA3LjMgOC41YzEuNCAxLjYgNC4yLS41IDIuOC0yLjRsLTcuNi04LjhjLS4xIDAtLjUtLjEtLjYgMGw3LjcgOC45Yy41LjUuNSAxLjEuMiAxLjUtLjEuMi0uMi4zLS4zLjUtLjEuMS0uMy4yLS41LjMtLjUuMS0xIDAtMS41LS41bC03LjUtOC44Yy0uMS4yLS4yLjYgMCAuOHptLTEuOS02LjYtMTQuMS0xNi4zYy0xLjYgMy4xIDMuMiAxMC4xIDYuMiAxMy44LjcuOSAxLjUgMiAyLjMgMi44LjkuOSAyLjYuOCAzLjIuMmwxIDEuMS4yLjIgMS40LTEuNXptMi43LTIuNC0xMy40IDE1LjRjLTEgMS4xLjYgMi42IDEuNiAxLjRsMTMuMy0xNS41YzEuNyAxLjQgMy45LS4xIDQuNC0uN2w3LjktOS4zYy41LS42LjYtMS40LjQtMi4xbC02LjEgNi45LS44LS41IDYuNC03LjJzLS4xLS4xLS4xLS4xbC0uNi0uNS02LjUgNy4zLS43LS42IDYuNi03LjQtLjYtLjVjMC0uMS0uMS0uMS0uMi0uMWwtNi41IDcuMy0uNS0uOCA2LjItNi45Yy0uNy0uMS0xLjUuMS0yIC43bC04LjEgOS4xYy0uNS42LTEuOSAyLjctLjcgNC4xeiIvPjwvc3ZnPg=='
        );
    }

    public function page_init()
    {
        $this->options = get_option('mobi2go-settings');

        register_setting(
            'mobi2go',
            'mobi2go-settings',
            array($this, 'sanitize')
        );

        add_settings_section(
            'mobi2go-settings-section',
            '',
            array($this, 'print_section_info'),
            'mobi2go'
        );

        add_settings_field(
            'api_key',
            'Mobi2Go API Key',
            array($this, 'apikey_callback'),
            'mobi2go',
            'mobi2go-settings-section'
        );

        if (!empty($this->options['api_key'])) {
            add_settings_field(
                'site',
                'Store',
                array($this, 'sitename_callback'),
                'mobi2go',
                'mobi2go-settings-section'
            );

            add_settings_field(
                'container',
                'Container ID',
                array($this, 'container_callback'),
                'mobi2go',
                'mobi2go-settings-section'
            );
        }
    }

    public function register_head()
    {
        echo '<link rel="stylesheet" type="text/css" href="' . plugin_dir_url(__FILE__) . 'css/admin_style.css" />' . "\n";
    }

    public function page()
    {
        $this->options = get_option('mobi2go-settings');

        if (isset($_GET['tab'])) {
            if (in_array($_GET['tab'], array('settings'))) {
                $active_tab = $_GET['tab'];
            } else {
                $active_tab = 'settings';
            }
        } else {
            $active_tab = 'settings';
        }
        ?>
        <div class="wrap">
            <?php if ($active_tab == 'settings') : ?>
                <form method="post" action="options.php">
                    <?php
                                settings_fields('mobi2go');
                                do_settings_sections('mobi2go');
                                submit_button();
                                ?>
                </form>

            <?php endif; ?>
        </div>
<?php
    }

    public function print_section_info()
    {
        echo '<h1>Mobi2Go Settings</h1>';
        echo '<p>';
        echo 'To use this plugin you will first need to create a Store with Mobi2Go and then enter your API Key below.<br />';
        echo 'You can sign-up for a free trial at ';
        echo '<a href="https://mobi2go.com/sign-up?utm_source=wordpress&utm_medium=settings&utm_campaign=wordpress-plugin" target="_blank">mobi2go.com</a>.<br />';
        echo '</p>';
        echo '<p>';
        echo 'To add to your Wordpress site create a new page and add the tag <code>[mobi2go]</code> to the content and publish the page.';
        echo '</p>';
    }

    public function sitename_callback()
    {
        $response = wp_remote_get(
            'https://mobi2go.com/api/1/account/headoffices',
            array(
                'headers' => array(
                    'Authorization' => 'Basic ' . base64_encode($this->options['api_key'] . ':'),
                ),
                'timeout' => 10,
            )
        );

        if (is_wp_error($response)) {
            $this->api_error = true;

            echo '<p class="description">
                Unable to retrieve stores, please make sure your
                API key is correct.
            </p>';

            return;
        }

        $json = json_decode(wp_remote_retrieve_body($response), true);

        if (empty($json) || isset($json['error'])) {
            echo '<p class="description">
                Unable to retrieve stores, please make sure your
                API key is correct.
            </p>';

            return;
        }

        echo '<select id="site" name="mobi2go-settings[site]">';

        foreach ($json as $store) {
            if (
                isset($this->options['site']) &&
                $store['domain_name'] == $this->options['site']
            ) {
                printf(
                    '<option value="%s" selected>%s</option>',
                    $store['domain_name'],
                    $store['display_name']
                );
            } else {
                printf(
                    '<option value="%s">%s</option>',
                    $store['domain_name'],
                    $store['display_name']
                );
            }
        }

        echo '</select>';
    }

    public function container_callback()
    {
        printf(
            '<input type="text" id="container" name="mobi2go-settings[container]" value="%s" class="regular-text code" />',
            empty($this->options['container']) ? 'mobi2go-ordering' : $this->options['container']
        );

        echo '<p class="description">
            This ID is applied to the div containing your embedded Mobi2Go storefront.
        </p>';
    }

    public function apikey_callback()
    {
        printf(
            '<input type="text" id="api_key" name="mobi2go-settings[api_key]" value="%s" class="regular-text code" />',
            empty($this->options['api_key']) ? '' : $this->options['api_key']
        );
        echo '<p class="description">
           You will need to <a
                href="https://www.mobi2go.com/admin/useraccount/my-account"
                target="_blank">generate an API Key</a> if you don’t have one already.
        </p>';
    }

    public function sanitize($input)
    {
        $clean = array();

        foreach ($input as $key => $value) {
            $clean[$key] = (string) strip_tags($value);
        }

        return $clean;
    }
}
