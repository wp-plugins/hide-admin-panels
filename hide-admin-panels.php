<?php
/*
Plugin Name: Hide Admin Panels
Plugin URI: http://www.wpxpand.com
Description: Allows you to hide admin panels for a specific user and/or role.
Author: WPXpand
Version: 0.9.8.2
Author URI: http://www.wpxpand.com
*/
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

/**
 * Hide Admin Panels Class
 *
 * @copyright 2009 Business Xpand
 * @license GPL v2.0
 * @author Steven Raynham
 * @version 0.9.8.2
 * @link http://www.businessxpand.com/
 * @since File available since Release 0.9
 */
class HideAdminPanels
{
    var $message;
    var $ozhActive;
    var $bxNews;

    /**
     * Construct the plugin
     *
     * @author Steven Raynham
     * @since 0.9.8
     *
     * @param void
     * @return null
     */
    function HideAdminPanels()
    {
        if ( is_admin() ) {
            $otherPlugins = get_option( 'active_plugins' );
            if ( in_array('ozh-admin-drop-down-menu/wp_ozh_adminmenu.php', $otherPlugins ) ) $this->ozhActive = true; else $this->ozhActive = false;
            add_action( 'init', array( &$this,'adminInit' ) );
            add_action( 'admin_menu', array( &$this, 'adminMenu' ) );
            add_action( 'admin_head', array( &$this, 'adminHead' ), 1000 );
            add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( &$this, 'pluginActionLinks' ), 10, 4 );
            if ( !class_exists( 'BxNews' ) ) include_once( dirname( __FILE__ ) . '/class-bx-news.php' );
            $this->bxNews = new BxNews( 'http://www.wpxpand.com/feeds/wordpress-plugins/', false );
        }
    }

   /**
     * Register the settings link on the plugin screen
     *
     * @author Steven Raynham
     * @since 0.9.8
     *
     * @param void
     * @return null
     */
    function pluginActionLinks( $links )
    {
        $settingsLink = '<a href="options-general.php?page=' . basename( __FILE__ ) . '">' . __('Settings') . '</a>';
		array_unshift( $links, $settingsLink );
		return $links;
	}

   /**
     * Initiate admin
     *
     * @author Steven Raynham
     * @since 0.9.8
     *
     * @param void
     * @return null
     */
    function adminInit()
    {
        wp_enqueue_script( 'jquery' );
        $this->message = '';
        if ( isset( $_POST['admin-panel-user'] ) ) $userId = $_POST['admin-panel-user']; else $userId = 1;
        if ( substr( $userId, 0, 4 ) == 'wpr-' ) {
            if ( !( $adminPanelOptions = get_option( 'hap_' . $userId ) ) ) $adminPanelOptions = array();
            if ( isset( $_POST['action'] ) && isset( $_POST['admin-panels-form'] ) ) {
                check_admin_referer( 'admin-panels-nonce', 'admin-panels-nonce' );
                switch ( $_POST['action'] ) {
                    case 'save':
                        if ( isset( $_POST['doaction_save'] ) ) {
                            delete_option( 'hap_' . $userId );
                            $adminPanelOptions = array();
                            if ( isset( $_POST['id_value'] ) && ( count( $_POST['id_value'] ) > 0 ) ) {
                                foreach ( $_POST['id_value'] as $name => $value ) {
                                    if ( !empty( $value ) ) {
                                        $adminPanelOptions[$name] = $value;
                                    }
                                }
                                if ( get_option( 'hap_' . $userId ) ) {
                                    update_option( 'hap_' . $userId, $adminPanelOptions );
                                } else {
                                    add_option( 'hap_' . $userId, $adminPanelOptions );
                                }
                            }
                        }
                        $this->message .= '<p>Admin panel options saved.</p>';
                        break;
                }
            }
        } else {
            if ( !( $adminPanelOptions = get_usermeta( $userId, 'admin-panels' ) ) ) $adminPanelOptions = array();
            if ( isset( $_POST['action'] ) && isset( $_POST['admin-panels-form'] ) ) {
                check_admin_referer( 'admin-panels-nonce', 'admin-panels-nonce' );
                switch ( $_POST['action'] ) {
                    case 'save':
                        if ( isset( $_POST['doaction_save'] ) ) {
                            delete_usermeta( $userId, 'admin-panels' );
                            $adminPanelOptions = array();
                            if ( isset( $_POST['id_value'] ) && ( count( $_POST['id_value'] ) > 0 ) ) {
                                foreach ( $_POST['id_value'] as $name => $value ) {
                                    if ( !empty( $value ) ) {
                                        $adminPanelOptions[$name] = $value;
                                    }
                                }
                                update_usermeta( $userId, 'admin-panels', $adminPanelOptions );
                            }
                        }
                        $this->message .= '<p>Admin panel options saved.</p>';
                        break;
                }
            }
        }
    }

   /**
     * Initiate admin menu
     *
     * @author Steven Raynham
     * @since 0.9
     *
     * @param void
     * @return null
     */
    function adminMenu()
    {
        add_options_page( __( 'Admin Panels' ), __( 'Admin Panels' ), 'level_7', basename(__FILE__), array( &$this, 'optionsPage' ) );
    }

   /**
     * Add admin header style
     *
     * @author Steven Raynham
     * @since 0.9.8.2
     *
     * @param void
     * @return null
     */
    function adminHead()
    {
        global $current_user;
        foreach ( $current_user->roles as $userRole ) {
            if ( $adminPanelOptions = get_option( 'hap_wpr-' . $userRole ) ) {
                foreach ( $adminPanelOptions as $name => $value ) {
                    if ( is_numeric( $value ) ) {
                        $value = $name;
                    }
                    $value = str_replace( "/", "\/", $value );
                    $css = ( $this->ozhActive ? '#ozhmenu ' : '' ) . 'li#' . $value . '{ display:none; }';
                    $cssArray[$css] = '';
                }
            }
        }
        if ( $adminPanelOptions = get_usermeta( $current_user->ID, 'admin-panels' ) ) {
            foreach ( $adminPanelOptions as $name => $value ) {
                if ( is_numeric( $value ) ) {
                    $value = $name;
                }
                $value = str_replace( "/", "\/", $value );
                $css = ( $this->ozhActive ? '#ozhmenu ' : '' ) . 'li#' . $value . '{ display:none; }';
                $cssArray[$css] = '';
            }
        }
        if ( isset( $cssArray ) && ( count( $cssArray ) > 0 ) ) {
            echo '<style type="text/css">' . "\r\n";
            foreach ( $cssArray as $css => $dummy ) {
                echo $css . "\r\n";
            }
            echo '</style>' . "\r\n";
        }
    }

    /**
     * Options page
     *
     * @author Steven Raynham, Marc
     * @since 0.9.8.2
     *
     * @param void
     * @return null
     */
    function optionsPage()
    {
        global $wpdb, $table_prefix, $current_user;
        $sql = "SELECT `ID`, `display_name` FROM `" . $wpdb->users . "`";
        $results = $wpdb->get_results( $wpdb->prepare( $sql ) );
        $users = '';
        if ( isset( $_REQUEST['admin-panel-user'] ) ) $userId = $_REQUEST['admin-panel-user']; else $userId = 1;
        if ( count( $results ) > 0 ) {
            foreach ( $results as $result ) {
                $users .= '<option value="' . $result->ID . '"' . ( ( $result->ID == $userId ) ? ' selected="selected"' : '' ) . '>' . $result->display_name . '</option>';
            }
        }
        if ( $roles = get_option( $table_prefix . 'user_roles' ) ) {
            $users .= '<option>&nbsp;</option>';
            foreach ( $roles as $roleName => $roleParameters ) {
                if ( $roleName != 'administrator' )
                    $users .= '<option value="wpr-' . $roleName . '"' . ( ( $userId == ( 'wpr-' . $roleName ) ) ? ' selected="selected"' : '' ) . '>' . $roleName . '</option>';
            }
        }

        if ( substr( $userId, 0, 4 ) == 'wpr-' ) {
            if ( !( $adminPanelOptions = get_option( 'hap_' . $userId ) ) ) $adminPanelOptions = array();
        } else {
            if ( !( $adminPanelOptions = get_usermeta( $userId, 'admin-panels' ) ) ) $adminPanelOptions = array();
        }
?><div class='wrap'>
    <h2><?php _e( 'Hide Admin Panels' ); ?></h2>
    <?php if ( !empty( $this->message ) ) { ?><div id="message" class="updated fade"><p><strong><?php _e( $this->message ); ?></strong></p></div><?php } ?>
    <h3><?php _e( 'Instructions' ); ?></h3>
    <ol>
        <li><?php _e( 'Select the user or the user role you wish to effect.' ); ?></li>
        <li><?php _e( 'Select the admin menu sections you wish to hide.' ); ?></li>
    </ol>
    <em><?php _e( 'Please note that you will not be able to hide the settings panel for the current user, or change the settings for the administrator role. This is to prevent mistakes with this plugin that prevent you from being able to fix them.' ); ?></em>
    <hr/>
    <div>
        <form method="post">
            <?php wp_nonce_field( 'admin-panels-nonce', 'admin-panels-nonce', true, true ); ?>
            <input type="hidden" name="action" value="save"/>
            <input type="hidden" name="admin-panels-form" value="true"/>
            <table>
                <tbody id="id_list">
                    <?php if ( !empty( $users ) ) { ?>
                    <tr>
                        <td align="right">
                            <label>Select user</label>:
                        </td>
                        <td>
                            <select name="admin-panel-user" onchange="submit(this);" id="admin-panel-user">
                                <?php echo $users; ?>
                            </select>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php $jsAdminPanelArray = ''; ?>
                    <?php foreach ( $adminPanelOptions as $name => $value ) { ?>
                    <?php if ( is_numeric( $value ) ) { $value = $name; } ?>
                    <?php $jsAdminPanelArray .= "excludeTags['" . $value . "'] = 1;"; ?>
                    <tr valign="top"><td align="right"><input type="checkbox" name="id_value[<?php echo $name; ?>]" value="<?php echo $value; ?>" checked="checked"/></td><td><?php echo $name; ?></td></tr>
                    <?php } ?>
                </tbody>
            </table>
            <p class="submit"><input class="button-primary" type="submit" id="submit_changes" name="doaction_save" value="<?php _e( 'Save changes' ); ?>"/></p>
        </form>
        <script type="text/javascript">
        /* <![CDATA[ */
            jQuery(document).ready(function() {
                var tags = jQuery('*');
                var excludeTags = [];
                var ids = [];
                var menuText;
                <?php echo $jsAdminPanelArray; ?>
                for ( var i in tags ) {
                    var tag = tags[i];
                    if ( tag.id ) {
                        if ( ( jQuery('#admin-panel-user').val() == '<?php echo $current_user->ID; ?>' )
                            && ( tag.id == '<?php echo ( $this->ozhActive ? 'oam_' : '' ); ?>menu-settings' ) )
                            tag.id = '';
                        if ( ( tag.id.search(/<?php echo ( $this->ozhActive ? 'oam_' : '' ); ?>menu-/i) > -1 ) || ( tag.id.search(/<?php echo ( $this->ozhActive ? 'oam_' : '' ); ?>toplevel_page_/i) > -1 ) ) {
                            if ( tag.id == '<?php echo ( $this->ozhActive ? 'oam_' : '' ); ?>menu-plugins' ) {
                                menuText = 'Plugins';
                            } else if ( tag.id == '<?php echo ( $this->ozhActive ? 'oam_' : '' ); ?>menu-comments' ) {
                                menuText = 'Comments';
                            } else {
                                var escapeTag = tag.id.replace(/\//g,'\\/');
                                menuText = jQuery('li#' + escapeTag).find('a.menu-top<?php echo ( $this->ozhActive ? ' .full' : '' ); ?>').text();
                            }
                            if ( excludeTags[tag.id] == undefined ) jQuery('#id_list').append( '<tr valign="top"><td align="right"><input type="checkbox" name="id_value[' + menuText + ']" value="' + tag.id + '"/></td><td>' + menuText + '</td></tr>' );
                        }
                    }
                }
            });
        /* ]]> */
        </script>
    </div>
</div>
<div class="wrap">
    <?php $this->bxNews->getFeed( '', array( 'http://wordpress.org/extend/plugins/hide-admin-panels/' ) ); ?>
</div>
<?php
    }
}
new HideAdminPanels;
