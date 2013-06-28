<?php
if(!class_exists('GFPDFEntryDetail'))
{
	class GFPDFEntryDetail {
	
		/* NEED THIS FUNCTION - BLD */
		public static function notes_grid($notes, $is_editable, $emails = null, $autoresponder_subject=""){
			if(sizeof($notes) > 0 && $is_editable && GFCommon::current_user_can_any("gravityforms_edit_entry_notes")){
				?>
				<div class="alignleft actions" style="padding:3px 0;">
					<label class="hidden" for="bulk_action"><?php _e(" Bulk action", "gravityforms") ?></label>
					<select name="bulk_action" id="bulk_action">
						<option value=''><?php _e(" Bulk action ", "gravityforms") ?></option>
						<option value='delete'><?php _e("Delete", "gravityforms") ?></option>
					</select>
					<?php
					$apply_button = '<input type="submit" class="button" value="' . __("Apply", "gravityforms") . '" onclick="jQuery(\'#action\').val(\'bulk\');" style="width: 50px;" />';
					echo apply_filters("gform_notes_apply_button", $apply_button);
					?>
				</div>
				<?php
			}
			?>
			<table class="widefat fixed entry-detail-notes" autosize="1" cellspacing="0">
				<?php
				if(!$is_editable){
				?>
				<thead>
					<tr>
						<th id="notes">Notes</th>
					</tr>
				</thead>
				<?php
				}
				?>
				<tbody id="the-comment-list" class="list:comment">
				<?php
				$count = 0;
				$notes_count = sizeof($notes);
				foreach($notes as $note){
					$count++;
					$is_last = $count >= $notes_count ? true : false;
					?>
					<tr valign="top">
						<?php
						if($is_editable && GFCommon::current_user_can_any("gravityforms_edit_entry_notes")){
						?>
							<th class="check-column" scope="row" style="padding:9px 3px 0 0">
								<input type="checkbox" value="<?php echo $note->id ?>" name="note[]"/>
							</th>
							<td colspan="2">
						<?php
						}
						else{
						?>
							<td class="entry-detail-note<?php echo $is_last ? " lastrow" : "" ?>">
						<?php
						}
						?>
								<div style="margin-top:4px;">
									<div class="note-avatar"><?php echo get_avatar($note->user_id, 48);?></div>
									<h6 class="note-author"> <?php echo esc_html($note->user_name)?></h6>
									<p style="line-height:130%; text-align:left; margin-top:3px;"><a href="mailto:<?php echo esc_attr($note->user_email)?>"><?php echo esc_html($note->user_email) ?></a><br />
									<?php _e("added on", "gravityforms"); ?> <?php echo esc_html(GFCommon::format_date($note->date_created, false)) ?></p>
								</div>
								<div class="detail-note-content"><?php echo esc_html($note->value) ?></div>
							</td>
	
					</tr>
					<?php
				}
				if($is_editable && GFCommon::current_user_can_any("gravityforms_edit_entry_notes")){
					?>
					<tr>
						<td colspan="3" style="padding:10px;" class="lastrow">
							<textarea name="new_note" style="width:100%; height:50px; margin-bottom:4px;"></textarea>
							<?php
							$note_button = '<input type="submit" name="add_note" value="' . __("Add Note", "gravityforms") . '" class="button" style="width:60px;" onclick="jQuery(\'#action\').val(\'add_note\');"/>';
							echo apply_filters("gform_addnote_button", $note_button);
	
							if(!empty($emails)){ ?>
								&nbsp;&nbsp;
								<span>
									<select name="gentry_email_notes_to" onchange="if(jQuery(this).val() != '') {jQuery('#gentry_email_subject_container').css('display', 'inline');} else{jQuery('#gentry_email_subject_container').css('display', 'none');}">
										<option value=""><?php _e("Also email this note to", "gravityforms") ?></option>
										<?php foreach($emails as $email){ ?>
											<option value="<?php echo $email ?>"><?php echo $email ?></option>
										<?php } ?>
									</select>
									&nbsp;&nbsp;
	
									<span id='gentry_email_subject_container' style="display:none;">
										<label for="gentry_email_subject"><?php _e("Subject:", "gravityforms") ?></label>
										<input type="text" name="gentry_email_subject" id="gentry_email_subject" value="<?php echo $autoresponder_subject ?>" style="width:35%"/>
									</span>
								</span>
							<?php } ?>
						</td>
					</tr>
				<?php
				}
				?>
				</tbody>
			</table>
			<?php
		}
		/* NEED THIS FUNCTION - BLD */
		public static function lead_detail_grid($form, $lead, $allow_display_empty_fields=false, $show_html=false, $show_page_name=false){
			$form_id = $form["id"];

			?>
			<div id="container">
				<h2 id="details" class="default"><?php echo $form["title"]?> <!--: <?php _e("Entry # ", "gravityforms") ?> <?php echo $lead["id"] ?> --></h2>
						
					<?php
					$count = 0;
					$field_count = sizeof($form["fields"]);
					
					$has_product_fields = false;

					$page_number = 0;
					foreach($form["fields"] as $field) {
						
						/*
						 * Check if we are to show the page names
						 */
						 if($show_page_name === true)
						 {
							if((int) $field['pageNumber'] !== $page_number)
							{								
								/*
								 * Display the page number
								 */
								?>
                                <h2 class="default entry-view-page-break"><?php echo $form['pagination']['pages'][$page_number]; ?></h2>
                                <?php
								/*
								 * Increment the page number
								 */
								$page_number++;	
							}
						 }
						
						$even = $odd = '';
						switch(RGFormsModel::get_input_type($field)){
						   case "section" :
						   
								if(!GFCommon::is_section_empty($field, $form, $lead) || $allow_display_empty_fields){
									$count++;
									$is_last = $count >= $field_count ? true : false;
									?>
									<h2 class="default entry-view-section-break<?php echo $is_last ? " lastrow" : ""?>"><?php echo esc_html(GFCommon::get_label($field))?></h2>
	
									<?php
								}
							break;
	
							case "captcha":
							case "password":
							case "page":
								//ignore captcha, html, password, page field
							break;
							case "html":
								if($show_html == true)
								{
									
									$count++;	
									$is_last = $count >= $field_count && !$has_product_fields ? true : false;
									$last_row = $is_last ? " lastrow" : "";
									$even = ($count%2) ? ' odd' : ' even';	
		
									$display_value = wpautop($field['content']);		
									
									$content = '<div class="entry-view-field-value' . $last_row . $even . '"><div class="value">' . $display_value . '</div></div>';
									$content = apply_filters("gform_field_content", $content, $field, $value, $lead["id"], $form["id"]);
	
									echo $content;																
								}
							break;
							case "signature":
								$value = RGFormsModel::get_lead_field_value($lead, $field);							
								$public_folder = RGFormsModel::get_upload_url_root() . 'signatures/';
								$server_folder = RGFormsModel::get_upload_root() . 'signatures/';
								$display_value = '<img src="'. $server_folder.$value .'" alt="Signature" width="100" height="60" />';	
								$is_last = $count >= $field_count ? true : false;	
								$last_row = $is_last ? " lastrow" : "";																			
														
								if(strlen($value) > 0 && (file_exists($server_folder.$value)) )
								{								
									 $content = '<div class="entry-view-field-value' . $last_row . $even . '"><div class="strong">' .  esc_html(GFCommon::get_label($field)) . '</div> <div class="value">' . $display_value . '</div></div>	';							
									
									echo $content;
								}
							break;
	
							default:
							
								//ignore product fields as they will be grouped together at the end of the grid
								if(GFCommon::is_product_field($field["type"])){
									$has_product_fields = true;
									continue;
								}
	
								$value = RGFormsModel::get_lead_field_value($lead, $field);
								$display_value = self::pdf_get_lead_field_display($field, $value, $lead["currency"]);
	
								$display_value = apply_filters("gform_entry_field_value", $display_value, $field, $lead, $form);					
	
								if( !empty($display_value) || $display_value === "0" || $allow_display_empty_fields){
									$count++;	
									$is_last = $count >= $field_count && !$has_product_fields ? true : false;
									$last_row = $is_last ? " lastrow" : "";
									$even = ($count%2) ? ' odd' : ' even';
	
									$display_value =  empty($display_value) && $display_value !== "0" ? "&nbsp;" : $display_value;
	
									$content = '<div class="entry-view-field-value' . $last_row . $even . '"><div class="strong">' .  esc_html(GFCommon::get_label($field)) . '</div> <div class="value">' . $display_value . '</div></div>';
	
									$content = apply_filters("gform_field_content", $content, $field, $value, $lead["id"], $form["id"]);
	
									echo $content;
										
								}
							break;
						}
						
					}
					$products = array();
					if($has_product_fields){
						
						   self::product_table($form, $lead);
						
					}
					?>
				</div>
			<?php
		}
		
		public static function product_table($form, $lead)
		{
			$products = GFCommon::get_product_fields($form, $lead, true);

			$form_id = $form['id'];
						if(!empty($products["products"])){
							?>
	
								<h2 class="default entry-view-field-name"><?php echo apply_filters("gform_order_label_{$form["id"]}", apply_filters("gform_order_label", __("Order", "gravityforms"), $form["id"]), $form["id"]) ?></h2>
	
									<table class="entry-products" autosize="1" cellspacing="0" width="97%">
									  <colgroup>
											  <col class="entry-products-col1" />
											  <col class="entry-products-col2" />
											  <col class="entry-products-col3" />
											  <col class="entry-products-col4" />
										</colgroup>
										<thead>
										  <tr>
											<th scope="col"><?php echo apply_filters("gform_product_{$form_id}", apply_filters("gform_product", __("Product", "gravityforms"), $form_id), $form_id) ?></th>
											<th scope="col" class="textcenter"><?php echo apply_filters("gform_product_qty_{$form_id}", apply_filters("gform_product_qty", __("Qty", "gravityforms"), $form_id), $form_id) ?></th>
											<th scope="col"><?php echo apply_filters("gform_product_unitprice_{$form_id}", apply_filters("gform_product_unitprice", __("Unit Price", "gravityforms"), $form_id), $form_id) ?></th>
											<th scope="col"><?php echo apply_filters("gform_product_price_{$form_id}", apply_filters("gform_product_price", __("Price", "gravityforms"), $form_id), $form_id) ?></th>
										  </tr>
										</thead>
										<tbody>
										<?php
	
											$total = 0;
											foreach($products["products"] as $product){
												?>
												<tr>
													<td>
														<div class="product_name"><?php echo esc_html($product["name"])?></div>
														
															<?php
															$price = GFCommon::to_number($product["price"]);
															if(is_array(rgar($product,"options"))){
																echo '<ul class="product_options">';
																$count = sizeof($product["options"]);
																$index = 1;
																foreach($product["options"] as $option){
																	$price += GFCommon::to_number($option["price"]);
																	$class = $index == $count ? " class='lastitem'" : "";
																	$index++;
																	?>
																	<li<?php echo $class?>><?php echo $option["option_label"]?></li>
																	<?php
																}
																echo '</ul>';
															}
															$subtotal = floatval($product["quantity"]) * $price;
															$total += $subtotal;
															?>
														
													</td>
													<td class="textcenter"><?php echo $product["quantity"] ?></td>
													<td><?php echo GFCommon::to_money($price, $lead["currency"]) ?></td>
													<td><?php echo GFCommon::to_money($subtotal, $lead["currency"]) ?></td>
												</tr>
												<?php
											}
											$total += floatval($products["shipping"]["price"]);
										?>
										
										
											<?php
											if(!empty($products["shipping"]["name"])){
											?>
												<tr>
													<td colspan="2" rowspan="2" class="emptycell">&nbsp;</td>
													<td class="textright shipping"><?php echo $products["shipping"]["name"] ?></td>
													<td class="shipping_amount"><?php echo GFCommon::to_money($products["shipping"]["price"], $lead["currency"])?>&nbsp;</td>
												</tr>
											<?php
											}
											?>
											<tr>
												<?php
												if(empty($products["shipping"]["name"])){
												?>
													<td colspan="2" class="emptycell">&nbsp;</td>
												<?php
												}
												?>
												<td class="textright grandtotal"><?php _e("Total", "gravityforms") ?></td>
												<td class="grandtotal_amount"><?php echo GFCommon::to_money($total, $lead["currency"])?></td>
											</tr>
                                            </tbody>
										
									</table>
	
			<?php 
			}
		}
		
		public static function format_date($date, $usa = false)
		{		
			$timestamp = strtotime($date);
			$new_date = (!$usa) ? date('j/n/Y', $timestamp) : date('n/j/Y', $timestamp);
			return $new_date;	
		}
		
		/* returns the form values as an array instead of pre-formated html */
		public static function lead_detail_grid_array($form, $lead, $allow_display_empty_fields=false){
			$form_id = $form["id"];
			$display_empty_fields = false;
			if($allow_display_empty_fields){
				$display_empty_fields = rgget("gf_display_empty_fields", $_COOKIE);
			}
			
			/*
			 * Add form_id and lead_id
			 */
			$form_array['form_id'] = $form_id;
			$form_array['entry_id'] = $lead['id'];
			
			$form_array['form_title'] = $form['title'];
			$form_array['date_created'] = self::format_date($lead['date_created']);		
			$form_array['date_created_usa'] = self::format_date($lead['date_created'], true);		
	
			$count = 0;
			$field_count = sizeof($form["fields"]);
			$has_product_fields = false;
		
					foreach($form["fields"] as $field){
						$display = '';

						switch(RGFormsModel::get_input_type($field)){
							case "section" :
							break;
	
							case "captcha":
							case "html":
								$form_array['html'][] = wpautop($field['content']);
							case "password":
							case "page":
								//ignore captcha, html, password, page field
							break;
							case "signature":
								$value = RGFormsModel::get_lead_field_value($lead, $field);
								$http_folder = RGFormsModel::get_upload_url_root(). 'signatures/';;
								$folder = RGFormsModel::get_upload_root() . 'signatures/';
								
								if(file_exists($folder.$value) !== false)
								{
									$form_array['signature'][] = '<img src="'. $folder.$value .'" alt="Signature" width="100" height="60" />';
									$form_array['signature_details'][] = array('img' => '<img src="'. $folder.$value .'" alt="Signature" width="100" height="60" />',
																	   'path' => $folder.$value,
																	   'url' => $http_folder.$value);
								}
	
								/*$count++;*/
								$is_last = $count >= $field_count && !$has_product_fields ? true : false;
								$last_row = $is_last ? " lastrow" : "";
							break;
							case "list":
								/*
								 * We want list to run both this and the deafult so don't call break.								 
								 * Get the list array and store it outside of [field]
								 */
								 $value = unserialize(RGFormsModel::get_lead_field_value($lead, $field));
								 $form_array['list'][] = $value; 
	
							default:
								//ignore product fields as they will be grouped together at the end of the grid
								if(GFCommon::is_product_field($field["type"])){
									$has_product_fields = true;
									continue;
								}
	
								$value = RGFormsModel::get_lead_field_value($lead, $field); 

								$display = self::get_lead_field_display($field, $value, $lead["currency"]);
								/* add data to field tag correctly */
								$form_array['field'][$field['id'].'.'.$field['label']] = $display;
								
								/* add ID incase want to use template on multiple duplicate forms with different field names */
								$form_array['field'][$field['id']] = $display;
								
								/* keep backwards compatibility */
								$form_array['field'][$field['label']] = $display;						
	
							break;
						}
						
					}
					
					$products = array();
					if($has_product_fields){
						$products = GFCommon::get_product_fields($form, $lead, true); 
						if(!empty($products["products"])){
							$total = 0;
							$subtotal = 0;
							foreach($products["products"] as $product) {	
								$price = GFCommon::to_number($product["price"]);
								
								if(is_array(rgar($product,"options"))){								
									$count = sizeof($product["options"]);
									$index = 1;
									foreach($product["options"] as $option){
										$price += GFCommon::to_number($option["price"]);
										$index++;
									}
								}
								/* calculate subtotal */
								$subtotal = floatval($product["quantity"]) * $price;
								$total += $subtotal;							
							
								$form_array['products'][] = array(
										'name' => esc_html($product['name']), 
										'price' => esc_html($product['price']), 
										'options' => $product['options'], 
										'quantity' => $product["quantity"], 
										'subtotal' => $subtotal);
							}						
							$total += floatval($products["shipping"]["price"]);						
							
							/* add to form data */
							$form_array['products_totals'] = array(
									'shipping' => $products["shipping"]["price"],
									'total'	   => $total
							);
						}
					}								
			return $form_array;
		}
		
		
		
		public static function get_lead_field_display($field, $value, $currency="", $use_text=false, $format="html", $media="screen"){
	
			if($field['type'] == 'post_category')
				$value = self::prepare_post_category_value($value, $field);
	
			switch(RGFormsModel::get_input_type($field)){
				case "name" :
					if(is_array($value)){
						$prefix = trim(rgget($field["id"] . ".2", $value));
						$first = trim(rgget($field["id"] . ".3", $value));
						$last = trim(rgget($field["id"] . ".6", $value));
						$suffix = trim(rgget($field["id"] . ".8", $value));
	
						return array('prefix' => $prefix, 'first' => $first, 'last' => $last, 'suffix' => $suffix);
					}
					else{
						return $value;
					}
	
				break;
				case "creditcard" :
					if(is_array($value)){
						$card_number = trim(rgget($field["id"] . ".1", $value));
						$card_type = trim(rgget($field["id"] . ".4", $value));
						$separator = $format == "html" ? "<br/>" : "\n";
						return empty($card_number) ? "" : $card_type . $separator . $card_number;
					}
					else{
						return "";
					}
				break;
	
				case "address" :
					if(is_array($value)){
						$street_value = trim(rgget($field["id"] . ".1", $value));
						$street2_value = trim(rgget($field["id"] . ".2", $value));
						$city_value = trim(rgget($field["id"] . ".3", $value));
						$state_value = trim(rgget($field["id"] . ".4", $value));
						$zip_value = trim(rgget($field["id"] . ".5", $value));
						$country_value = trim(rgget($field["id"] . ".6", $value));
	
						$line_break = $format == "html" ? "<br />" : "\n";
	
						$address_display_format = apply_filters("gform_address_display_format", "default");
	
						$address['street'] = $street_value;
						$address['street2'] = $street2_value;
						$address['city'] =  $city_value;
						$address['state'] =  $state_value;
						$address['zip'] = $zip_value;
						$address['country'] = $country_value;
	
						return $address;
					}
					else{
						return "";
					}
				break;
	
				case "email" :
					return GFCommon::is_valid_email($value) && $format == "html" ? $value : $value;
				break;
	
				case "website" :
					return GFCommon::is_valid_url($value) && $format == "html" ? $value : $value;
				break;
	
				case "checkbox" :
					if(is_array($value)){
	
						$items = array();
	
						foreach($value as $key => $item){
							if(!empty($item)){
								switch($format){
									case "text" :
										$items[] = GFCommon::selection_display($item, $field, $currency, $use_text);
									break;
	
									default:
										$items[] = GFCommon::selection_display($item, $field, $currency, $use_text);
									break;
								}
							}
						}
						if(empty($items)){
							return "";
						}
						else if($format == "text"){
							/*return substr($items, 0, strlen($items)-2); //removing last comma*/
						}
						else{
							return $items;
						}
					}
					else{
						return $value;
					}
				break;
	
				case "post_image" :
					$ary = explode("|:|", $value);
					$url = count($ary) > 0 ? $ary[0] : "";
					$title = count($ary) > 1 ? $ary[1] : "";
					$caption = count($ary) > 2 ? $ary[2] : "";
					$description = count($ary) > 3 ? $ary[3] : "";
	
					if(!empty($url)){
						$url = str_replace(" ", "%20", $url);
	
						$value = array('url' => $url,
									   'path' => str_replace(site_url().'/', ABSPATH, $url),
									   'title' => $title,
									   'caption' => $caption,
									   'description' => $description);
					}
					return $value;
	
				case "fileupload" :
					$file_path = $value;
					if(!empty($file_path)){
						$info = pathinfo($file_path);
						$file_path = esc_attr(str_replace(" ", "%20", $file_path));
						$value = $file_path;
					}
					return $value;
				break;
	
				case "date" :
					return GFCommon::date_display($value, rgar($field, "dateFormat"));
				break;
	
				case "radio" :
				case "select" :
					return GFCommon::selection_display($value, $field, $currency, $use_text);
				break;
	
				case "multiselect" :
					if(empty($value) || $format == "text")
						return $value;
	
					$value = explode(",", $value);
	
					$items = '';
					foreach($value as $item){
						$items[] = GFCommon::selection_display($item, $field, $currency, $use_text);
					}
	
					if(sizeof($items) == 1)
					{
						return $items[0];	
					}
					return $items;
					
	
				break;
	
				case "calculation" :
				case "singleproduct" :
					if(is_array($value)){
						$product_name = trim($value[$field["id"] . ".1"]);
						$price = trim($value[$field["id"] . ".2"]);
						$quantity = trim($value[$field["id"] . ".3"]);
	
						$product = $product_name . ", " . __("Qty: ", "gravityforms") . $quantity . ", " . __("Price: ", "gravityforms") . $price;
						return $product;
					}
					else{
						return "";
					}
				break;
	
				case "number" :
					return GFCommon::format_number($value, rgar($field, "numberFormat"));
				break;
	
				case "singleshipping" :
				case "donation" :
				case "total" :
				case "price" :
					return GFCommon::to_money($value, $currency);
	
				case "list" :
					if(empty($value))
						return "";
					$value = unserialize($value);
	
					$has_columns = is_array($value[0]);
	
					if(!$has_columns){
						$items = array();
						foreach($value as $key => $item){
							if(!empty($item)){
								switch($format){
									case "text" :
										$items[] = $item;
									break;
									case "url" :
										$items[] = $item;
									break;
									default :
										if($media == "email"){
											$items[] = $item;
										}
										else{
											$items[] = $item;
										}
									break;
								}
							}
						}
	
						if(empty($items)){
							return "";
						}
						else if($format == "text"){
						   /* return substr($items, 0, strlen($items)-2); //removing last comma*/
							return $items;					   
						}
						else if($format == "url"){
							/*return substr($items, 0, strlen($items)-1); //removing last comma*/
							return $items;						
						}
						else if($media == "email"){
							return $items;
						}
						else{
							return $items;
						}
					}
					else if(is_array($value)){
						$columns = array_keys($value[0]);
	
						$list = "";
	
						switch($format){
							case "text" :
								$is_first_row = true;
								foreach($value as $item){
									if(!$is_first_row)
										$list .= "\n\n" . $field["label"] . ": ";
									$list .= implode(",", array_values($item));
	
									$is_first_row = false;
								}
							break;
	
							case "url" :
								foreach($value as $item){
									$list .= implode("|", array_values($item)) . ",";
								}
								if(!empty($list))
									$list = substr($list, 0, strlen($list)-1);
							break;
	
							default :
								if($media == "email"){
									$list = "<table autosize='1' class='gfield_list' style='border-top: 1px solid #DFDFDF; border-left: 1px solid #DFDFDF; border-spacing: 0; padding: 0; margin: 2px 0 6px; width: 100%'><thead><tr>";
	
									//reading columns from entry data
									foreach($columns as $column){
										$list .= "<th style='background-image: none; border-right: 1px solid #DFDFDF; border-bottom: 1px solid #DFDFDF; padding: 6px 10px; font-family: sans-serif; font-size: 12px; font-weight: bold; background-color: #F1F1F1; color:#333; text-align:left'>" . esc_html($column) . "</th>";
									}
									$list .= "</tr></thead>";
	
									$list .= "<tbody style='background-color: #F9F9F9'>";
									foreach($value as $item){
										$list .= "<tr>";
										foreach($columns as $column){
											$val = rgar($item, $column);
											$list .= "<td style='padding: 6px 10px; border-right: 1px solid #DFDFDF; border-bottom: 1px solid #DFDFDF; border-top: 1px solid #FFF; font-family: sans-serif; font-size:12px;'>{$val}</td>";
										}
	
										$list .="</tr>";
									}
	
									$list .="</tbody></table>";
								}
								else{
									$list = "<table class='gfield_list' autosize='1'><thead><tr>";
	
									//reading columns from entry data
									foreach($columns as $column){
										$list .= "<th>" . esc_html($column) . "</th>";
									}
									$list .= "</tr></thead>";
	
									$list .= "<tbody>";
									foreach($value as $item){
										$list .= "<tr>";
										foreach($columns as $column){
											$val = rgar($item, $column);
											$list .= "<td>".htmlspecialchars($val)."</td>";
										}
	
										$list .="</tr>";
									}
	
									$list .="</tbody></table>";
								}
							break;
						}
	
						return $list;
					}
					return "";
				break;
	
				default :
					if (!is_array($value))
					{
						return nl2br($value);
					}
				break;
			}
		}	
	
		function pdf_get_lead_field_display($field, $value, $currency="", $use_text=false, $format="html", $media="screen"){
		
				if($field['type'] == 'post_category')
					$value = self::prepare_post_category_value($value, $field);
		
				switch(RGFormsModel::get_input_type($field)){
					case "name" :
						if(is_array($value)){
							$prefix = trim(rgget($field["id"] . ".2", $value));
							$first = trim(rgget($field["id"] . ".3", $value));
							$last = trim(rgget($field["id"] . ".6", $value));
							$suffix = trim(rgget($field["id"] . ".8", $value));
		
							$name = $prefix;
							$name .= !empty($name) && !empty($first) ? " $first" : $first;
							$name .= !empty($name) && !empty($last) ? " $last" : $last;
							$name .= !empty($name) && !empty($suffix) ? " $suffix" : $suffix;
		
							return $name;
						}
						else{
							return $value;
						}
		
					break;
					case "creditcard" :
						if(is_array($value)){
							$card_number = trim(rgget($field["id"] . ".1", $value));
							$card_type = trim(rgget($field["id"] . ".4", $value));
							$separator = $format == "html" ? "<br/>" : "\n";
							return empty($card_number) ? "" : $card_type . $separator . $card_number;
						}
						else{
							return "";
						}
					break;
		
					case "address" :
						if(is_array($value)){
							$street_value = trim(rgget($field["id"] . ".1", $value));
							$street2_value = trim(rgget($field["id"] . ".2", $value));
							$city_value = trim(rgget($field["id"] . ".3", $value));
							$state_value = trim(rgget($field["id"] . ".4", $value));
							$zip_value = trim(rgget($field["id"] . ".5", $value));
							$country_value = trim(rgget($field["id"] . ".6", $value));
		
							$line_break = $format == "html" ? "<br />" : "\n";
		
							$address_display_format = apply_filters("gform_address_display_format", "default");
							if($address_display_format == "zip_before_city"){
								/*
								Sample:
								3333 Some Street
								suite 16
								2344 City, State
								Country
								*/
		
								$addr_ary = array();
								$addr_ary[] = $street_value;
		
								if(!empty($street2_value))
									$addr_ary[] = $street2_value;
		
								$zip_line = trim($zip_value . " " . $city_value);
								$zip_line .= !empty($zip_line) && !empty($state_value) ? ", {$state_value}" : $state_value;
								$zip_line = trim($zip_line);
								if(!empty($zip_line))
									$addr_ary[] = $zip_line;
		
								if(!empty($country_value))
									$addr_ary[] = $country_value;
		
								$address = implode("<br />", $addr_ary);
		
							}
							else{
								$address = $street_value;
								$address .= !empty($address) && !empty($street2_value) ? $line_break . $street2_value : $street2_value;
								$address .= !empty($address) && (!empty($city_value) || !empty($state_value)) ? $line_break. $city_value : $city_value;
								$address .= !empty($address) && !empty($city_value) && !empty($state_value) ? ", $state_value" : $state_value;
								$address .= !empty($address) && !empty($zip_value) ? " $zip_value" : $zip_value;
								$address .= !empty($address) && !empty($country_value) ? $line_break . $country_value : $country_value;
							}
		
							return $address;
						}
						else{
							return "";
						}
					break;
		
					case "email" :
						return GFCommon::is_valid_email($value) && $format == "html" ? "<a href='mailto:$value'>$value</a>" : $value;
					break;
		
					case "website" :
						return GFCommon::is_valid_url($value) && $format == "html" ? "<a href='$value' target='_blank'>$value</a>" : $value;
					break;
		
					case "checkbox" :
						if(is_array($value)){
		
							$items = '';
		
							foreach($value as $key => $item){
								if(!empty($item)){
									switch($format){
										case "text" :
											$items .= GFCommon::selection_display($item, $field, $currency, $use_text) . ", ";
										break;
		
										default:
											$items .= "<li>" . GFCommon::selection_display($item, $field, $currency, $use_text) . "</li>";
										break;
									}
								}
							}
							if(empty($items)){
								return "";
							}
							else if($format == "text"){
								return substr($items, 0, strlen($items)-2); //removing last comma
							}
							else{
								return "<ul class='bulleted'>$items</ul>";
							}
						}
						else{
							return $value;
						}
					break;
		
					case "post_image" :
						$ary = explode("|:|", $value);
						$url = count($ary) > 0 ? $ary[0] : "";
						$title = count($ary) > 1 ? $ary[1] : "";
						$caption = count($ary) > 2 ? $ary[2] : "";
						$description = count($ary) > 3 ? $ary[3] : "";
		
						if(!empty($url)){
							$url = str_replace(" ", "%20", $url);
							switch($format){
								case "text" :
									$value = $url;
									$value .= !empty($title) ? "\n\n" . $field["label"] . " (" . __("Title", "gravityforms") . "): " . $title : "";
									$value .= !empty($caption) ? "\n\n" . $field["label"] . " (" . __("Caption", "gravityforms") . "): " . $caption : "";
									$value .= !empty($description) ? "\n\n" . $field["label"] . " (" . __("Description", "gravityforms") . "): " . $description : "";
								break;
		
								default :
									$path = str_replace(site_url().'/', ABSPATH, $url);
									$value = "<a href='$url' target='_blank' title='" . __("Click to view", "gravityforms") . "'><img src='$path' width='100' /></a>";
									$value .= !empty($title) ? "<div>Title: $title</div>" : "";
									$value .= !empty($caption) ? "<div>Caption: $caption</div>" : "";
									$value .= !empty($description) ? "<div>Description: $description</div>": "";
		
								break;
							}
						}
						return $value;
		
					case "fileupload" :
						$file_path = $value;
						if(!empty($file_path)){
							$info = pathinfo($file_path);
							$file_path = esc_attr(str_replace(" ", "%20", $file_path));
							$value = $format == "text" ? $file_path : "<a href='$file_path' target='_blank' title='" . __("Click to view", "gravityforms") . "'>" . $info["basename"] . "</a>";
						}
						return $value;
					break;
		
					case "date" :
						return GFCommon::date_display($value, rgar($field, "dateFormat"));
					break;
		
					case "radio" :
					case "select" :
						return GFCommon::selection_display($value, $field, $currency, $use_text);
					break;
		
					case "multiselect" :
						if(empty($value) || $format == "text")
							return $value;
		
						$value = explode(",", $value);
		
						$items = '';
						foreach($value as $item){
							$items .= "<li>" . GFCommon::selection_display($item, $field, $currency, $use_text) . "</li>";
						}
		
						return "<ul class='bulleted'>{$items}</ul>";
		
					break;
		
					case "calculation" :
					case "singleproduct" :
						if(is_array($value)){
							$product_name = trim($value[$field["id"] . ".1"]);
							$price = trim($value[$field["id"] . ".2"]);
							$quantity = trim($value[$field["id"] . ".3"]);
		
							$product = $product_name . ", " . __("Qty: ", "gravityforms") . $quantity . ", " . __("Price: ", "gravityforms") . $price;
							return $product;
						}
						else{
							return "";
						}
					break;
		
					case "number" :
						return GFCommon::format_number($value, rgar($field, "numberFormat"));
					break;
		
					case "singleshipping" :
					case "donation" :
					case "total" :
					case "price" :
						return GFCommon::to_money($value, $currency);
		
					case "list" :
						if(empty($value))
							return "";
						$value = unserialize($value);
		
						$has_columns = is_array($value[0]);
		
						if(!$has_columns){
							$items = '';
							foreach($value as $key => $item){
								if(!empty($item)){
									switch($format){
										case "text" :
											$items .= $item . ", ";
										break;
										case "url" :
											$items .= $item . ",";
										break;
										default :
											if($media == "email"){
												$items .= "<li>".htmlspecialchars($item)."</li>";
											}
											else{
												$items .= "<li>".htmlspecialchars($item)."</li>";
											}
										break;
									}
								}
							}
		
							if(empty($items)){
								return "";
							}
							else if($format == "text"){
								return substr($items, 0, strlen($items)-2); //removing last comma
							}
							else if($format == "url"){
								return substr($items, 0, strlen($items)-1); //removing last comma
							}
							else if($media == "email"){
								return "<ul class='bulleted'>{$items}</ul>";
							}
							else{
								return "<ul class='bulleted'>{$items}</ul>";
							}
						}
						else if(is_array($value)){
							$columns = array_keys($value[0]);
		
							$list = "";
		
							switch($format){
								case "text" :
									$is_first_row = true;
									foreach($value as $item){
										if(!$is_first_row)
											$list .= "\n\n" . $field["label"] . ": ";
										$list .= implode(",", array_values($item));
		
										$is_first_row = false;
									}
								break;
		
								case "url" :
									foreach($value as $item){
										$list .= implode("|", array_values($item)) . ",";
									}
									if(!empty($list))
										$list = substr($list, 0, strlen($list)-1);
								break;
		
								default :
									if($media == "email"){
										$list = "<table autosize='1' class='gfield_list' style='border-top: 1px solid #DFDFDF; border-left: 1px solid #DFDFDF; border-spacing: 0; padding: 0; margin: 2px 0 6px; width: 100%'><thead><tr>";
		
										//reading columns from entry data
										foreach($columns as $column){
											$list .= "<th style='background-image: none; border-right: 1px solid #DFDFDF; border-bottom: 1px solid #DFDFDF; padding: 6px 10px; font-family: sans-serif; font-size: 12px; font-weight: bold; background-color: #F1F1F1; color:#333; text-align:left'>" . esc_html($column) . "</th>";
										}
										$list .= "</tr></thead>";
		
										$list .= "<tbody style='background-color: #F9F9F9'>";
										foreach($value as $item){
											$list .= "<tr>";
											foreach($columns as $column){
												$val = rgar($item, $column);
												$list .= "<td style='padding: 6px 10px; border-right: 1px solid #DFDFDF; border-bottom: 1px solid #DFDFDF; border-top: 1px solid #FFF; font-family: sans-serif; font-size:12px;'>{$val}</td>";
											}
		
											$list .="</tr>";
										}
		
										$list .="</tbody></table>";
									}
									else{
										$list = "<table autosize='1' class='gfield_list'><thead><tr>";
		
										//reading columns from entry data
										foreach($columns as $column){
											$list .= "<th>" . esc_html($column) . "</th>";
										}
										$list .= "</tr></thead>";
		
										$list .= "<tbody>";
										foreach($value as $item){
											$list .= "<tr>";
											foreach($columns as $column){
												$val = rgar($item, $column);
												$list .= "<td>".htmlspecialchars($val)."</td>";
											}
		
											$list .="</tr>";
										}
		
										$list .="</tbody></table>";
									}
								break;
							}
		
							return $list;
						}
						return "";
					break;
		
					default :
						if (!is_array($value))
						{
							return nl2br($value);
						}
					break;
				}
			}
	}
}