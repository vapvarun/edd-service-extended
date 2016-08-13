<?php if (!empty($_GET['edd-verify-success'])) : ?>
    <p class="edd-account-verified edd_success">
        <?php _e('Your account has been successfully verified!', 'easy-digital-downloads'); ?>
    </p>
    <?php
endif;
?>
<div style="color: #fff !important; background: #515a63; text-align: center; padding: 6px;" class="center-downloads"><h2>Your Downloads</h2></div>
<?php
/**
 * This template is used to display the download history of the current user.
 */
$purchases = edd_get_users_purchases(get_current_user_id(), 20, true, 'any');
if ($purchases) :
    do_action('edd_before_download_history');
    ?>
    <table id="edd_user_history" class="edd-table">
        <thead>
            <tr class="edd_download_history_row">
                <?php do_action('edd_download_history_header_start'); ?>
                <th class="edd_download_download_name edd_name_th"><?php _e('Download Name', 'easy-digital-downloads'); ?></th>
                <?php if (!edd_no_redownload()) : ?>
                    <th class="edd_download_download_files edd_file_th"><?php _e('Files', 'easy-digital-downloads'); ?></th>
                <?php endif; //End if no redownload ?>
                <th class="edd_download_download_price"><?php _e('Price', 'easy-digital-downloads'); ?></th>
                <th class="edd_download_download_price"><?php _e('Date', 'easy-digital-downloads'); ?></th>
                <th class="edd_download_download_view"><?php _e('View Items', 'easy-digital-downloads'); ?></th>
                <?php do_action('edd_download_history_header_end'); ?>
            </tr>
        </thead>
        <?php
        foreach ($purchases as $payment) : setup_postdata($payment);
            $downloads = edd_get_payment_meta_cart_details($payment->ID, true);
            $purchase_data = edd_get_payment_meta($payment->ID);
            $email = edd_get_payment_user_email($payment->ID);
            if ($downloads) :
                foreach ($downloads as $download) :
                    // Skip over Bundles. Products included with a bundle will be displayed individually
                    if (edd_is_bundled_product($download['id']))
                        continue;
                    ?>

                    <tr class="edd_download_history_row">
                        <?php
                        $price_id = edd_get_cart_item_price_id($download);
                        $download_files = edd_get_download_files($download['id'], $price_id);
                        $name = get_the_title($download['id']);

                        // Retrieve and append the price option name
                        if (!empty($price_id)) {
                            $name .= ' - ' . edd_get_price_option_name($download['id'], $price_id, $payment->ID);
                        }

                        do_action('edd_download_history_row_start', $payment->ID, $download['id']);
                        ?>
                        <td class="edd_download_download_name"><?php echo esc_html($name); ?></td>

                        <?php if (!edd_no_redownload()) : ?>
                            <td class="edd_download_download_files">
                                <?php
                                if (edd_is_payment_complete($payment->ID)) :

                                    if ($download_files) :

                                        foreach ($download_files as $filekey => $file) :

                                            $download_url = edd_get_download_file_url($purchase_data['key'], $email, $filekey, $download['id'], $price_id);
                                            ?>

                                            <div class="edd_download_file">
                                                <a href="<?php echo esc_url($download_url); ?>" class="edd_download_file_link">
                                                    <?php echo edd_get_file_name($file); ?>
                                                </a>
                                            </div>

                                            <?php
                                            do_action('edd_download_history_files', $filekey, $file, $id, $payment->ID, $purchase_data);
                                        endforeach;

                                    else :
                                        _e('No downloadable files found.', 'easy-digital-downloads');
                                    endif; // End if payment complete

                                else :
                                    ?>
                                    <span class="edd_download_payment_status">
                                        <?php printf(__('Payment status is %s', 'easy-digital-downloads'), edd_get_payment_status($payment, true)); ?>
                                    </span>
                                <?php
                                endif; // End if $download_files
                                ?>
                            </td>
                            <td class="edd_purchase_amount">
                                <span class="edd_purchase_amount"><?php echo edd_currency_filter(edd_format_amount(edd_get_payment_amount($payment->ID))); ?></span>
                            </td>
                            <td class="edd_purchase_date"><?php echo date_i18n(get_option('date_format'), strtotime(get_post_field('post_date', $payment->ID))); ?></td>
                            <td class="edd_purchase_details">
                                <?php if ($payment->post_status != 'publish') : ?>
                                    <span class="edd_purchase_status <?php echo $payment->post_status; ?>"><?php echo edd_get_payment_status($payment, true); ?></span>
                                    <a href="<?php echo esc_url(add_query_arg('payment_key', edd_get_payment_key($payment->ID), edd_get_success_page_uri())); ?>">&raquo;</a>
                                <?php else:
                                    ?>
                                    <a href="<?php echo esc_url(add_query_arg('payment_key', edd_get_payment_key($payment->ID), edd_get_success_page_uri('single-order'))); ?>"><?php _e('<img src="' . get_site_url() . '/wp-content/plugins/edd-service-extended/includes/images/page_view.png" alt="HTML5 Icon" width="30" height="30" align="center">', 'easy-digital-downloads'); ?></a>
                                <?php endif; ?>
                            </td>
                            <?php
                        endif; // End if ! edd_no_redownload()

                        do_action('edd_download_history_row_end', $payment->ID, $download['id']);
                        ?>
                    </tr>
                    <?php
                endforeach; // End foreach $downloads
            endif; // End if $downloads
        endforeach;
        ?>
    </table>
    <div id="edd_download_history_pagination" class="edd_pagination navigation">
        <?php
        $big = 999999;
        echo paginate_links(array(
            'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
            'format' => '?paged=%#%',
            'current' => max(1, get_query_var('paged')),
            'total' => ceil(edd_count_purchases_of_customer() / 20) // 20 items per page
        ));
        ?>
    </div>
    <?php do_action('edd_after_download_history'); ?>
<?php else : ?>
    <p class="edd-no-downloads"><?php _e('You have not purchased any downloads', 'easy-digital-downloads'); ?></p>
<?php endif; ?>

<?php
$user = wp_get_current_user();
$allowed_roles = array('editor', 'shop_manager', 'shop_accountant', 'shop_worker', 'shop_vendor');
if (array_intersect($allowed_roles, $user->roles)) {
    ?>
    <div style="color: #fff !important; background: #515a63; text-align: center; padding: 6px;" class="center-block"><h2> Customer Orders </h2></div>
    <?php
    /**
     * This template is used to display the download history of the current user.
     */
    $user_id = get_current_user_id();
    $args = array(
        'post_type' => 'download',
        'post_status' => 'publish',
        'order' => 'ASC',
        'author' => $user_id
    );

    $products = get_posts($args);
    $downloaded_user_id_array = array();
    if (!empty($products)) {
        foreach ($products as $key => $product) {
            $puchase_ids = get_payment_ids($product->ID);
            foreach ($puchase_ids as $puchase_ids_key => $puchase_id) {
                $downloaded_user_id = get_post_meta($puchase_id, '_edd_payment_user_id');
                $downloaded_user_id_array[] = $downloaded_user_id[0];
            }
        }
        $downloaded_user_id_array = array_unique($downloaded_user_id_array);
        for ($i = 0; $i <= count($downloaded_user_id_array); $i++) {
            $customer_purchases = edd_get_users_purchases($downloaded_user_id_array[$i], 20, true, 'any');
            $author_obj = get_user_by('id', $downloaded_user_id_array[$i]);
            $customer_name = $author_obj->data->display_name;
            if ($customer_name == '') {
                $customer_name = $author_obj->data->user_nicename;
            }

            if ($customer_purchases) :
                do_action('edd_before_download_history');
                ?>

                <table id="edd_user_history" class="edd-table">
                    <?php //if ($i == 0) {  ?>
                    <thead>
                        <tr class="edd_download_history_row">
                            <?php do_action('edd_download_history_header_start'); ?>
                            <th class="edd_download_download_name edd_name_th"><?php _e('Download Name', 'easy-digital-downloads'); ?></th>
                            <?php if (!edd_no_redownload()) : ?>
                                <th class="edd_download_download_files edd_file_th"><?php _e('Files', 'easy-digital-downloads'); ?></th>
                            <?php endif; //End if no redownload   ?>
                            <th class="edd_download_download_price"><?php _e('Price', 'easy-digital-downloads'); ?></th>
                            <th class="edd_download_download_price"><?php _e('Date', 'easy-digital-downloads'); ?></th>
                            <th class="edd_download_download_view"><?php _e('View Items', 'easy-digital-downloads'); ?></th>
                            <?php do_action('edd_download_history_header_end'); ?>
                        </tr>
                    </thead>
                    <?php //}  ?>
                    <div style="color: #005580; background: #f0f5f5;"><h4> Customer Name : <?php echo $customer_name; ?> </h4></div>
                    <?php
                    foreach ($customer_purchases as $payment) : setup_postdata($payment);

                        $downloads = edd_get_payment_meta_cart_details($payment->ID, true);
                        $purchase_data = edd_get_payment_meta($payment->ID);
                        $email = edd_get_payment_user_email($payment->ID);
                        if ($downloads) :
                            foreach ($downloads as $download) :
//                  
                                // Skip over Bundles. Products included with a bundle will be displayed individually
                                if (edd_is_bundled_product($download['id']))
                                    continue;
                                ?>

                                <tr class="edd_download_history_row">
                                    <?php
                                    $price_id = edd_get_cart_item_price_id($download);
                                    $download_files = edd_get_download_files($download['id'], $price_id);
                                    $name = get_the_title($download['id']);

                                    // Retrieve and append the price option name
                                    if (!empty($price_id)) {
                                        $name .= ' - ' . edd_get_price_option_name($download['id'], $price_id, $payment->ID);
                                    }

                                    do_action('edd_download_history_row_start', $payment->ID, $download['id']);
                                    ?>
                                    <td class="edd_download_download_name"><?php echo esc_html($name); ?></td>

                                    <?php if (!edd_no_redownload()) : ?>
                                        <td class="edd_download_download_files">
                                            <?php
                                            if (edd_is_payment_complete($payment->ID)) :

                                                if ($download_files) :

                                                    foreach ($download_files as $filekey => $file) :

                                                        $download_url = edd_get_download_file_url($purchase_data['key'], $email, $filekey, $download['id'], $price_id);
                                                        ?>

                                                        <div class="edd_download_file">
                                                            <a href="<?php echo esc_url($download_url); ?>" class="edd_download_file_link">
                                                                <?php echo edd_get_file_name($file); ?>
                                                            </a>
                                                        </div>

                                                        <?php
                                                        do_action('edd_download_history_files', $filekey, $file, $id, $payment->ID, $purchase_data);
                                                    endforeach;

                                                else :
                                                    _e('No downloadable files found.', 'easy-digital-downloads');
                                                endif; // End if payment complete

                                            else :
                                                ?>
                                                <span class="edd_download_payment_status">
                                                    <?php printf(__('Payment status is %s', 'easy-digital-downloads'), edd_get_payment_status($payment, true)); ?>
                                                </span>
                                            <?php
                                            endif; // End if $download_files
                                            ?>
                                        </td>
                                        <td class="edd_purchase_amount">
                                            <span class="edd_purchase_amount"><?php echo edd_currency_filter(edd_format_amount(edd_get_payment_amount($payment->ID))); ?></span>
                                        </td>
                                        <td class="edd_purchase_date"><?php echo date_i18n(get_option('date_format'), strtotime(get_post_field('post_date', $payment->ID))); ?></td>
                                        <td class="edd_purchase_details">
                                            <?php if ($payment->post_status != 'publish') : ?>
                                                <span class="edd_purchase_status <?php echo $payment->post_status; ?>"><?php echo edd_get_payment_status($payment, true); ?></span>
                                                <a href="<?php echo esc_url(add_query_arg('payment_key', edd_get_payment_key($payment->ID), edd_get_success_page_uri())); ?>">&raquo;</a>
                                            <?php else:
                                                ?>
                                                <a href="<?php echo esc_url(add_query_arg('payment_key', edd_get_payment_key($payment->ID), edd_get_success_page_uri('single-order'))); ?>"><?php _e('<img src="' . get_site_url() . '/wp-content/plugins/edd-service-extended/includes/images/page_view.png" alt="HTML5 Icon" width="30" height="30" align="center">', 'easy-digital-downloads'); ?></a>
                                            <?php endif; ?>
                                        </td>
                                        <?php
                                    endif; // End if ! edd_no_redownload()

                                    do_action('edd_download_history_row_end', $payment->ID, $download['id']);
                                    ?>
                                </tr>
                                <?php
                            endforeach; // End foreach $downloads
                        endif; // End if $downloads

                    endforeach;
                    ?>
                </table>
                <div id="edd_download_history_pagination" class="edd_pagination navigation">
                    <?php
                    $big = 999999;
                    echo paginate_links(array(
                        'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                        'format' => '?paged=%#%',
                        'current' => max(1, get_query_var('paged')),
                        'total' => ceil(edd_count_purchases_of_customer() / 20) // 20 items per page
                    ));
                    ?>
                </div>
                <?php do_action('edd_after_download_history'); ?>
            <?php endif; ?>


            <?php
        }
    }
}
?> 