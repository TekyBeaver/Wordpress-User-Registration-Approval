<?php

/*
    Plugin Name: Users Activation
    Description: This plugin will allow us to block and unblock users
    Version: 1.0

*/



/*********************************************************************************

Add a new column in the users table

***********************************************************************************/

add_filter('manage_users_columns','add_user_status');


function add_user_status($column){

    $column['user_status'] = 'Status';

    return $column;

}

/*********************************************************************************

Output the current status of the user in the new column if it is active or not

***********************************************************************************/

add_filter('manage_users_custom_column','status_column_value',10,3);

function status_column_value($output,$column_name,$user_id){

    $userDatas = get_userdata($user_id);

    if($userDatas->roles[0] != 'administrator' && $column_name = 'user_status' ){

        if(get_usermeta($user_id,'user_status') == 'activated' ){

            return 'Active';


        }else{

            return 'Desactivated';

        }

    }

    return $output;

}

/*********************************************************************************

Add a select form to the user-edit page that blocks and unlocks the customer

***********************************************************************************/

add_action('show_user_profile','addStatusField');
add_action('edit_user_profile', 'addStatusField');

function addStatusField($user){

    $userStatus = get_user_meta($user->ID,'user_status',true);
    
?>

    <h3>Activate / Desactivate this User</h3>

    <select name="user_status">

        <option value="activate" <?php if(!empty($userStatus)){ ?> selected <?php } ?> >Activate</option>

        <option value="desactivate" <?php if(empty($userStatus)){ ?> selected <?php } ?> >Desactivate</option>

    </select>

<?php

}

/*********************************************************************************

Saving the value coming from the select form when is emtpy it means blocked

***********************************************************************************/

add_action('personal_options_update','updateUserStatus');
add_action('edit_user_profile_update','updateUserStatus');

function updateUserStatus($user_id){

    if(isset($_REQUEST['user_status'])){

        if($_REQUEST['user_status'] == 'activate'){

            update_user_meta($user_id, 'user_status','activated');

        }else{

            update_user_meta($user_id,'user_status','');

        }


    }

}

/*********************************************************************************

Block automatic login after woocommerce user registration

Block connections until the administrator has activated the user 

***********************************************************************************/

add_filter('woocommerce_registration_auth_new_customer','__return_false');

add_filter('wp_authenticate_user','check_user_status',10,2);

function check_user_status($user,$password){

    if($user instanceof WP_User){

        $userDatas = get_userdata($user->ID);

        if($userDatas->roles[0] != 'administrator' ){

            $userSatut = get_user_meta($user->ID,'user_status',true);

            if(empty($userSatut)){

                return new WP_Error('user_activation','Your account is not yet activated');

            }

        }

    }

    return $user;
}

?>

