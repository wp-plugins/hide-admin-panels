<?php
/*
Plugin Name: Hide Admin Panels
Plugin URI: http://www.businessxpand.com
Description: Allows you to hide admin panels for a specific user
Author: Business Xpand
Version: 0.9
Author URI: http://www.businessxpand.com
*/
//error_reporting(E_ALL);
//ini_set('display_errors', '1');

/**
 * Hide Admin Panels Class
 *
 * @copyright 2009 Business Xpand
 * @license GPL v2.0
 * @author Steven Raynham
 * @version 0.9
 * @link http://www.businessxpand.com/
 * @since File available since Release 0.9
 */
class HideAdminPanels
{
    var $message;

    /**
     * Construct the plugin
     *
     * @author Steven Raynham
     * @since 0.9
     *
     * @param void
     * @return null
     */
    function HideAdminPanels()
    {
        if ( is_admin() ) {
            add_action( 'init', array( &$this,'adminInit' ) );
            add_action( 'admin_menu', array( &$this, 'adminMenu' ) );
            add_action( 'admin_head', array( &$this, 'adminHead' ) );
        }
    }

   /**
     * Initiate admin
     *
     * @author Steven Raynham
     * @since 0.9
     *
     * @param void
     * @return null
     */
    function adminInit()
    {
        wp_enqueue_script( 'jquery' );
        $this->message = '';
        if ( isset( $_POST['admin-panel-user'] ) ) $userId = $_POST['admin-panel-user']; else $userId = 1;
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
                            $this->message .= '<p>Admin panel options saved.</p>';
                        }
                    }
                    break;
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
        add_options_page( __( 'Admin Panels' ), __( 'Admin Panels' ), 'level_7', basename(__FILE__), array( &$this,'optionsPage' ) );
    }

   /**
     * Add admin header style
     *
     * @author Steven Raynham
     * @since 0.9
     *
     * @param void
     * @return null
     */
    function adminHead()
    {
        $userId = wp_get_current_user()->ID;
        if ( !( $adminPanelOptions = get_usermeta( $userId, 'admin-panels' ) ) ) $adminPanelOptions = array();
?><style>
<?php foreach ( $adminPanelOptions as $name => $value ) { ?>
    #<?php echo $name; ?> { display:none; }
<?php } ?>
</style><?php
    }

    /**
     * Options page
     *
     * @author Steven Raynham
     * @since 0.9
     *
     * @param void
     * @return null
     */
    function optionsPage()
    {
        global $wpdb;
        $sql = "SELECT `ID`, `display_name` FROM `" . $wpdb->users . "`";
        $results = $wpdb->get_results( $wpdb->prepare( $sql ) );
        $users = '';
        if ( isset( $_REQUEST['admin-panel-user'] ) ) $userId = $_REQUEST['admin-panel-user']; else $userId = 1;
        if ( count( $results ) > 0 ) {
            foreach ( $results as $result ) {
                $users .= '<option value="' . $result->ID . '"' . ( ( $result->ID == $userId ) ? ' selected="selected"' : '' ) . '>' . $result->display_name . '</option>';
            }
        }
        if ( !( $adminPanelOptions = get_usermeta( $userId, 'admin-panels' ) ) ) $adminPanelOptions = array();
?><div class='wrap'>
    <h2><?php _e( 'Hide Admin Panels' ); ?></h2>
    <?php if ( !empty( $message ) ) { ?><div id="message" class="updated fade"><p><strong><?php _e( $message ); ?></strong></p></div><?php } ?>
    <h3><?php _e( 'Instructions' ); ?></h3>
    <ul>
        <li><?php _e( 'Select the admin menu sections you wish to hide, simple as that.' ); ?></li>
    </ul>
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
                            <select name="admin-panel-user" onchange="submit(this);">
                                <?php echo $users; ?>
                            </select>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php $jsAdminPanelArray = ''; ?>
                    <?php foreach ( $adminPanelOptions as $name => $value ) { ?>
                    <?php $jsAdminPanelArray .= "excludeTags['" . $name . "'] = 1;"; ?>
                    <tr valign="top"><td align="right"><input type="checkbox" name="id_value[<?php echo $name; ?>]" value="1"<?php echo ( ($value == '1') ? 'checked="checked"' : '' ); ?>/></td><td><?php echo $name; ?></td></tr>
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
                <?php echo $jsAdminPanelArray; ?>
                for ( var i in tags ) {
                    var tag = tags[i];
                    if ( tag.id ) {
                        //ids.push( tag.id );
                        var tagId = tag.id;
                        if ( tag.id.search(/menu-/i) > -1 ) {
                            if ( excludeTags[tag.id] == undefined ) jQuery('#id_list').append( '<tr valign="top"><td align="right"><input type="checkbox" name="id_value[' + tag.id + ']" value="1"/></td><td>' + tag.id + '</td></tr>' );
                        }
                    }
                }
            });
        /* ]]> */
        </script>
    </div>
</div><?php
    }
}
$hideAdminPanels = new HideAdminPanels;