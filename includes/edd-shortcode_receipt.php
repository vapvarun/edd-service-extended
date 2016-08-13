<?php
/**
 * This template is used to display the purchase summary with [wbcom_edd_receipt]
 */
global $edd_receipt_args;

$payment = get_post($edd_receipt_args['id']);

$meta = edd_get_payment_meta($payment->ID);
$cart = edd_get_payment_meta_cart_details($payment->ID, true);
$user = edd_get_payment_meta_user_info($payment->ID);
$email = edd_get_payment_user_email($payment->ID);
$status = edd_get_payment_status($payment, true);
?>

<?php if (filter_var($edd_receipt_args['products'], FILTER_VALIDATE_BOOLEAN)) : ?>

    <h3><?php echo apply_filters('edd_payment_receipt_products_title', __('Products', 'easy-digital-downloads')); ?></h3>

    <table id="edd_purchase_receipt_products" class="edd-table">
        <thead>
        <th><?php _e('Name', 'easy-digital-downloads'); ?></th>
        <?php if (edd_use_skus()) { ?>
            <th><?php _e('SKU', 'easy-digital-downloads'); ?></th>
        <?php } ?>
        <?php if (edd_item_quantities_enabled()) : ?>
            <th><?php _e('Quantity', 'easy-digital-downloads'); ?></th>
        <?php endif; ?>
        <th><?php _e('Price', 'easy-digital-downloads'); ?></th>
    </thead>

    <tbody>
        <?php if ($cart) : ?>
            <?php foreach ($cart as $key => $item) :
                ?>

                <?php if (!apply_filters('edd_user_can_view_receipt_item', true, $item)) : ?>
                    <?php continue; // Skip this item if can't view it ?>
                <?php endif; ?>

                <?php if (empty($item['in_bundle'])) : ?>
                    <tr>
                        <td>

                            <?php
                            $price_id = edd_get_cart_item_price_id($item);
                            $download_files = edd_get_download_files($item['id'], $price_id);
                            ?>

                            <div class="edd_purchase_receipt_product_name">
                                <?php echo esc_html($item['name']); ?>
                                <?php if (edd_has_variable_prices($item['id']) && !is_null($price_id)) : ?>
                                    <span class="edd_purchase_receipt_price_name">&nbsp;&ndash;&nbsp;<?php echo edd_get_price_option_name($item['id'], $price_id, $payment->ID); ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if ($edd_receipt_args['notes']) : ?>
                                <div class="edd_purchase_receipt_product_notes"><?php echo wpautop(edd_get_product_notes($item['id'])); ?></div>
                            <?php endif; ?>

                            <?php if (edd_is_payment_complete($payment->ID) && edd_receipt_show_download_files($item['id'], $edd_receipt_args, $item)) : ?>
                                <ul class="edd_purchase_receipt_files">
                                    <?php
                                    if (!empty($download_files) && is_array($download_files)) :

                                        foreach ($download_files as $filekey => $file) :

                                            $download_url = edd_get_download_file_url($meta['key'], $email, $filekey, $item['id'], $price_id);
                                            ?>
                                            <li class="edd_download_file">
                                                <a href="<?php echo esc_url($download_url); ?>" class="edd_download_file_link"><?php echo edd_get_file_name($file); ?></a>
                                            </li>
                                            <?php
                                            do_action('edd_receipt_files', $filekey, $file, $item['id'], $payment->ID, $meta);
                                        endforeach;

                                    elseif (edd_is_bundled_product($item['id'])) :

                                        $bundled_products = edd_get_bundled_products($item['id']);

                                        foreach ($bundled_products as $bundle_item) :
                                            ?>
                                            <li class="edd_bundled_product">
                                                <span class="edd_bundled_product_name"><?php echo get_the_title($bundle_item); ?></span>
                                                <ul class="edd_bundled_product_files">
                                                    <?php
                                                    $download_files = edd_get_download_files($bundle_item);

                                                    if ($download_files && is_array($download_files)) :

                                                        foreach ($download_files as $filekey => $file) :

                                                            $download_url = edd_get_download_file_url($meta['key'], $email, $filekey, $bundle_item, $price_id);
                                                            ?>
                                                            <li class="edd_download_file">
                                                                <a href="<?php echo esc_url($download_url); ?>" class="edd_download_file_link"><?php echo edd_get_file_name($file); ?></a>
                                                            </li>
                                                            <?php
                                                            do_action('edd_receipt_bundle_files', $filekey, $file, $item['id'], $bundle_item, $payment->ID, $meta);

                                                        endforeach;
                                                    else :
                                                        echo '<li>' . __('No downloadable files found for this bundled item.', 'easy-digital-downloads') . '</li>';
                                                    endif;
                                                    ?>
                                                </ul>
                                            </li>
                                            <?php
                                        endforeach;

                                    else :
                                        echo '<li>' . apply_filters('edd_receipt_no_files_found_text', __('No downloadable files found.', 'easy-digital-downloads'), $item['id']) . '</li>';
                                    endif;
                                    ?>
                                </ul>
                            <?php endif; ?>

                        </td>
                        <?php if (edd_use_skus()) : ?>
                            <td><?php echo edd_get_download_sku($item['id']); ?></td>
                        <?php endif; ?>
                        <?php if (edd_item_quantities_enabled()) { ?>
                            <td><?php echo $item['quantity']; ?></td>
                        <?php } ?>
                        <td>
                            <?php if (empty($item['in_bundle'])) : // Only show price when product is not part of a bundle ?>
                                <?php echo edd_currency_filter(edd_format_amount($item['price'])); ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php if (( $fees = edd_get_payment_fees($payment->ID, 'item'))) : ?>
            <?php foreach ($fees as $fee) : ?>
                <tr>
                    <td class="edd_fee_label"><?php echo esc_html($fee['label']); ?></td>
                    <?php if (edd_item_quantities_enabled()) : ?>
                        <td></td>
                    <?php endif; ?>
                    <td class="edd_fee_amount"><?php echo edd_currency_filter(edd_format_amount($fee['amount'])); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        <tr><?php echo '<td style="text-align:center;" colspan="2"><img class="img-responsive center-block" src="' . get_site_url() . '/wp-content/plugins/edd-service-extended/includes/images/expedited_delivery_icon.png" alt="HTML5 Icon" width="30" height="30" align="center"></td>'; ?></tr>
        <tr>


            <td align="center" colspan="2" class="center-ordertext"><?php
                $meta = get_post_meta($payment->ID, '_edd_payment_meta', true);
                $download_id = $meta['downloads'][0]['id'];
                $auth = get_post($download_id);
                $authid = $auth->post_author;
                $user_by_id = get_user_by('ID', $authid);
                $display = $user_by_id->data->display_name;
                if ($display != '') {
                    $display_name = $display;
                } else {
                    $display_name = $user_by_id->data->user_nicename;
                }
                echo '<h4 style="color: #005580;" class="center-block-h4"><span>Order Started</span></h4><br>';
                echo '<p style="text-align:center;"><b>' . $display_name . '</b>' . ' will soon start working on your order.</p>';
                ?>

            </td>
        </tr>
    </tbody>

    </table>
    <?php echo do_shortcode('[add_user_comment_edd]'); ?>
    <table>
        <tbody>

            <?php
            $close = get_post_meta($payment->ID, 'edd_user_order_thread');
            if ($close[0] == 'close') {
                echo '<tr><td style="text-align:center;"><img class="img-responsive center-block" src="' . get_site_url() . '/wp-content/plugins/edd-service-extended/includes/images/PO_approved.png" alt="HTML5 Icon" width="30" height="30" align="center"></td></tr>';
                echo '<tr><td class="center-ordertext"><h4 style="color: #005580;" class="center-block"><span>Order Completed</span></h4><br></td></tr>';
            }
            ?>

        </tbody>
    </table>

<?php endif; ?>