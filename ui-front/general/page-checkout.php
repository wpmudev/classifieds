<?php
/**
* The template for displaying the Checkout page.
* You can override this file in your active theme.
*
* @package Classifieds
* @subpackage UI Front
* @since Classifieds 2.0
*/

global $current_user;

$current_user = wp_get_current_user();

$options = $this->get_options();

$step = get_query_var('checkout_step');
$step = (empty($step)) ? 'terms' : $step;

$step = (empty($_GET['step'])) ? $step : $_GET['step'];

$error = get_query_var('checkout_error');
$error = (empty($error)) ? '' : $error;

if ( $this->is_full_access() && $step != 'success' && $step != 'api_call_error' ) {
	_e( 'You already have access to create ads.', $this->text_domain );
	$step = '';
}

//STEP = DISABLED
if ( $step == 'disabled' ): 
_e( 'This feature is currently disabled by the system administrator.', $this->text_domain );
elseif ( !empty($error) ): ?>
<div class="invalid-login"><?php echo $error; ?></div>
<?php endif; 

//STEP = TERMS
if ( $step == 'terms'): ?>

<!-- Begin Terms -->
<form action="#" method="post"  class="checkout">

	<strong><?php _e( 'Cost of Service', $this->text_domain ); ?></strong>
	<table <?php do_action( 'billing_invalid' ); ?>>

		<?php if($this->use_credits && ! $this->is_full_access() ): ?>
		<tr>
			<td><label for="billing_type"><?php _e( 'Buy Credits', $this->text_domain ) ?></label></td>
			<td>
				<input type="radio" name="billing_type" value="credits" checked="checked" />
				<select name="credits">
					<?php
					for ( $i = 1; $i <= 10; $i++ ):
					$credits = 10 * $i;
					$amount = $credits * $options['payments']['cost_credit'];
					?>
					<option value="<?php echo $credits; ?>" <?php selected(! empty($_POST['credits_cost'] ) && $_POST['credits_cost'] == $amount ); ?> >
						<?php echo $credits; ?> Credits for <?php echo sprintf( "%01.2f", $amount) . ' ' . $options['payment_types']['paypal']['currency']; ?>
					</option>
					<?php endfor; ?>
				</select>
			</td>
		</tr>
		<?php endif; ?>

		<?php if ( $this->use_recurring ) : ?>
		<tr>
			<td <?php do_action( 'billing_invalid' ); ?>>

				<label for="type_recurring"><?php echo (empty( $options['payments']['recurring_name'] ) ) ? '' : $options['payments']['recurring_name']; ?></label>
			</td>
			<td>
				<input type="radio" name="billing_type" id="type_recurring" value="recurring" <?php checked( ! empty($_POST['billing_type'] ) && $_POST['billing_type'] == 'recurring' ); ?> />
				<span>
					<?php
					$bastr    = empty( $options['payments']['recurring_cost'] ) ? '' : $options['payments']['recurring_cost'] . ' ';
					$bastr .= $options['payment_types']['paypal']['currency'];
					$bastr .= __( ' per ', $this->text_domain );
					$bastr .= ( ! empty( $options['payments']['billing_frequency'] ) && $options['payments']['billing_frequency'] != 1 ) ? $options['payments']['billing_frequency'] . ' ' : '';
					$bastr .= empty( $options['payments']['billing_period'] ) ? '' : $options['payments']['billing_period'];
					$bastr .= ($options['payments']['billing_frequency'] > 1) ? __(' period', $this->text_domain) : '';
					echo $bastr;
					?>
				</span>
				<input type="hidden" name="recurring_cost" value="<?php echo ( empty( $options['payments']['recurring_cost'] ) ) ? '0' : $options['payments']['recurring_cost']; ?>" />
				<input type="hidden" name="billing_agreement" value="<?php echo ( empty( $options['payments']['billing_agreement'] ) ) ? '' : $options['payments']['billing_agreement']; ?>" />
			</td>
		</tr>
		<?php endif; ?>

		<?php if ( $this->use_one_time ): ?>
		<tr>
			<td<?php do_action( 'billing_invalid' ); ?>><label for="billing_type"><?php echo $options['payments']['one_time_txt']; ?></label></td>
			<td>
				<input type="radio" name="billing_type" value="one_time" <?php if ( isset( $_POST['billing_type'] ) && $_POST['billing_type'] == 'one_time' ) echo 'checked="checked"'; ?> /> <?php echo $options['payments']['one_time_cost']; ?> <?php echo $options['payment_types']['paypal']['currency']; ?>
				<input type="hidden" name="one_time_cost" value="<?php echo $options['payments']['one_time_cost']; ?>" />
			</td>
		</tr>
		<?php endif;?>
	</table>
	<br />

	<?php if(! empty($options['payments']['tos_txt'])): ?>

	<strong><?php _e( 'Terms of Service', $this->text_domain ); ?></strong>
	<table>
		<tr>
			<td><div class="terms"><?php echo nl2br( $options['payments']['tos_txt'] ); ?></div></td>
		</tr>
	</table>
	<br />

	<table  <?php do_action( 'tos_invalid' ); ?> >
		<tr>
			<td>
				<label for="tos_agree">
					<input type="checkbox" id="tos_agree" name="tos_agree" value="1" <?php checked( ! empty( $_POST['tos_agree'] ) ); ?> />
					<?php _e( 'I agree with the Terms of Service', $this->text_domain ); ?>
				</label>
			</td>
		</tr>
	</table>

	<?php else: ?>
	<input type="hidden" id="tos_agree" name="tos_agree" value="1" />
	<?php endif; ?>

	<div class="submit">
		<input type="submit" name="terms_submit" value="<?php _e( 'Continue', $this->text_domain ); ?>" />
	</div>
</form>

<?php if ( ! empty($error) ): ?>
<div class="invalid-login"><?php echo $error; ?></div>
<?php endif; ?>
<!-- End Terms -->










<?php elseif( $step == 'payment_method' ): ?>
<!-- Begin Payment Method -->

<?php if( $this->use_free): ?>
<strong><?php _e( 'Posting Classified Ads is Free when Logged In' ); ?></strong>
<?php else: ?>

<form action="#" method="post"  class="checkout">
	<strong><?php _e('Choose Payment Method', $this->text_domain ); ?></strong>
	<table class="form-table">
		<?php if( $this->use_paypal ): ?>
		<tr>
			<td><label for="payment_method"><?php _e( 'PayPal', $this->text_domain ); ?></label></td>
			<td>
				<input type="radio" name="payment_method" value="paypal"/>
				<img  src="https://www.paypal.com/en_US/i/logo/PayPal_mark_37x23.gif" border="0" alt="Acceptance Mark">
			</td>
		</tr>
		<?php endif; ?>
		<?php if( $this->use_authorizenet ): ?>
		<tr>
			<td><label for="payment_method"><?php _e( 'Credit Card', $this->text_domain ); ?></label></td>
			<td>
				<input type="radio" name="payment_method" value="cc" />
				<img  src="<?php echo CF_PLUGIN_URL; ?>ui-front/general/images/cc-logos-small.jpg" border="0" alt="Solution Graphics">
			</td>
		</tr>
		<?php endif; ?>
	</table>

	<div class="submit">
		<input type="submit" name="payment_method_submit" value="<?php _e( 'Continue', $this->text_domain ); ?>" />
	</div>
</form>
<?php endif; ?>
<!--End Payment Method -->









<?php elseif ( $step == 'cc_details' ): ?>
<!--Begin CC Details -->

<?php
$countries = array (
"" => "Select One",
"US" => "United States",
"CA" => "Canada",
"-" => "----------",
"AF" => "Afghanistan",
"AL" => "Albania",
"DZ" => "Algeria",
"AS" => "American Samoa",
"AD" => "Andorra",
"AO" => "Angola",
"AI" => "Anguilla",
"AQ" => "Antarctica",
"AG" => "Antigua and Barbuda",
"AR" => "Argentina",
"AM" => "Armenia",
"AW" => "Aruba",
"AU" => "Australia",
"AT" => "Austria",
"AZ" => "Azerbaidjan",
"BS" => "Bahamas",
"BH" => "Bahrain",
"BD" => "Bangladesh",
"BB" => "Barbados",
"BY" => "Belarus",
"BE" => "Belgium",
"BZ" => "Belize",
"BJ" => "Benin",
"BM" => "Bermuda",
"BT" => "Bhutan",
"BO" => "Bolivia",
"BA" => "Bosnia-Herzegovina",
"BW" => "Botswana",
"BV" => "Bouvet Island",
"BR" => "Brazil",
"IO" => "British Indian Ocean Territory",
"BN" => "Brunei Darussalam",
"BG" => "Bulgaria",
"BF" => "Burkina Faso",
"BI" => "Burundi",
"KH" => "Cambodia",
"CM" => "Cameroon",
"CV" => "Cape Verde",
"KY" => "Cayman Islands",
"CF" => "Central African Republic",
"TD" => "Chad",
"CL" => "Chile",
"CN" => "China",
"CX" => "Christmas Island",
"CC" => "Cocos (Keeling) Islands",
"CO" => "Colombia",
"KM" => "Comoros",
"CG" => "Congo",
"CK" => "Cook Islands",
"CR" => "Costa Rica",
"HR" => "Croatia",
"CU" => "Cuba",
"CY" => "Cyprus",
"CZ" => "Czech Republic",
"DK" => "Denmark",
"DJ" => "Djibouti",
"DM" => "Dominica",
"DO" => "Dominican Republic",
"TP" => "East Timor",
"EC" => "Ecuador",
"EG" => "Egypt",
"SV" => "El Salvador",
"GQ" => "Equatorial Guinea",
"ER" => "Eritrea",
"EE" => "Estonia",
"ET" => "Ethiopia",
"FK" => "Falkland Islands",
"FO" => "Faroe Islands",
"FJ" => "Fiji",
"FI" => "Finland",
"CS" => "Former Czechoslovakia",
"SU" => "Former USSR",
"FR" => "France",
"FX" => "France (European Territory)",
"GF" => "French Guyana",
"TF" => "French Southern Territories",
"GA" => "Gabon",
"GM" => "Gambia",
"GE" => "Georgia",
"DE" => "Germany",
"GH" => "Ghana",
"GI" => "Gibraltar",
"GB" => "Great Britain",
"GR" => "Greece",
"GL" => "Greenland",
"GD" => "Grenada",
"GP" => "Guadeloupe (French)",
"GU" => "Guam (USA)",
"GT" => "Guatemala",
"GN" => "Guinea",
"GW" => "Guinea Bissau",
"GY" => "Guyana",
"HT" => "Haiti",
"HM" => "Heard and McDonald Islands",
"HN" => "Honduras",
"HK" => "Hong Kong",
"HU" => "Hungary",
"IS" => "Iceland",
"IN" => "India",
"ID" => "Indonesia",
"INT" => "International",
"IR" => "Iran",
"IQ" => "Iraq",
"IE" => "Ireland",
"IL" => "Israel",
"IT" => "Italy",
"CI" => "Ivory Coast (Cote D&#39;Ivoire)",
"JM" => "Jamaica",
"JP" => "Japan",
"JO" => "Jordan",
"KZ" => "Kazakhstan",
"KE" => "Kenya",
"KI" => "Kiribati",
"KW" => "Kuwait",
"KG" => "Kyrgyzstan",
"LA" => "Laos",
"LV" => "Latvia",
"LB" => "Lebanon",
"LS" => "Lesotho",
"LR" => "Liberia",
"LY" => "Libya",
"LI" => "Liechtenstein",
"LT" => "Lithuania",
"LU" => "Luxembourg",
"MO" => "Macau",
"MK" => "Macedonia",
"MG" => "Madagascar",
"MW" => "Malawi",
"MY" => "Malaysia",
"MV" => "Maldives",
"ML" => "Mali",
"MT" => "Malta",
"MH" => "Marshall Islands",
"MQ" => "Martinique (French)",
"MR" => "Mauritania",
"MU" => "Mauritius",
"YT" => "Mayotte",
"MX" => "Mexico",
"FM" => "Micronesia",
"MD" => "Moldavia",
"MC" => "Monaco",
"MN" => "Mongolia",
"MS" => "Montserrat",
"MA" => "Morocco",
"MZ" => "Mozambique",
"MM" => "Myanmar",
"NA" => "Namibia",
"NR" => "Nauru",
"NP" => "Nepal",
"NL" => "Netherlands",
"AN" => "Netherlands Antilles",
"NT" => "Neutral Zone",
"NC" => "New Caledonia (French)",
"NZ" => "New Zealand",
"NI" => "Nicaragua",
"NE" => "Niger",
"NG" => "Nigeria",
"NU" => "Niue",
"NF" => "Norfolk Island",
"KP" => "North Korea",
"MP" => "Northern Mariana Islands",
"NO" => "Norway",
"OM" => "Oman",
"PK" => "Pakistan",
"PW" => "Palau",
"PA" => "Panama",
"PG" => "Papua New Guinea",
"PY" => "Paraguay",
"PE" => "Peru",
"PH" => "Philippines",
"PN" => "Pitcairn Island",
"PL" => "Poland",
"PF" => "Polynesia (French)",
"PT" => "Portugal",
"PR" => "Puerto Rico",
"QA" => "Qatar",
"RE" => "Reunion (French)",
"RO" => "Romania",
"RU" => "Russian Federation",
"RW" => "Rwanda",
"GS" => "S. Georgia & S. Sandwich Isls.",
"SH" => "Saint Helena",
"KN" => "Saint Kitts & Nevis Anguilla",
"LC" => "Saint Lucia",
"PM" => "Saint Pierre and Miquelon",
"ST" => "Saint Tome (Sao Tome) and Principe",
"VC" => "Saint Vincent & Grenadines",
"WS" => "Samoa",
"SM" => "San Marino",
"SA" => "Saudi Arabia",
"SN" => "Senegal",
"SC" => "Seychelles",
"SL" => "Sierra Leone",
"SG" => "Singapore",
"SK" => "Slovak Republic",
"SI" => "Slovenia",
"SB" => "Solomon Islands",
"SO" => "Somalia",
"ZA" => "South Africa",
"KR" => "South Korea",
"ES" => "Spain",
"LK" => "Sri Lanka",
"SD" => "Sudan",
"SR" => "Suriname",
"SJ" => "Svalbard and Jan Mayen Islands",
"SZ" => "Swaziland",
"SE" => "Sweden",
"CH" => "Switzerland",
"SY" => "Syria",
"TJ" => "Tadjikistan",
"TW" => "Taiwan",
"TZ" => "Tanzania",
"TH" => "Thailand",
"TG" => "Togo",
"TK" => "Tokelau",
"TO" => "Tonga",
"TT" => "Trinidad and Tobago",
"TN" => "Tunisia",
"TR" => "Turkey",
"TM" => "Turkmenistan",
"TC" => "Turks and Caicos Islands",
"TV" => "Tuvalu",
"UG" => "Uganda",
"UA" => "Ukraine",
"AE" => "United Arab Emirates",
"GB" => "United Kingdom",
"UY" => "Uruguay",
"MIL" => "USA Military",
"UM" => "USA Minor Outlying Islands",
"UZ" => "Uzbekistan",
"VU" => "Vanuatu",
"VA" => "Vatican City State",
"VE" => "Venezuela",
"VN" => "Vietnam",
"VG" => "Virgin Islands (British)",
"VI" => "Virgin Islands (USA)",
"WF" => "Wallis and Futuna Islands",
"EH" => "Western Sahara",
"YE" => "Yemen",
"YU" => "Yugoslavia",
"ZR" => "Zaire",
"ZM" => "Zambia",
"ZW" => "Zimbabwe",
);

?>

<form action="#" method="post" class="checkout" id="cfcheckout">

	<strong><?php _e( 'Payment Details', $this->text_domain ); ?></strong>
	<div class="clear"></div>
	<table class="form-table">
		<tr>
			<td><label for="cc_email"><?php _e( 'Email Address for Credit Card', $this->text_domain ); ?>:</label></td>
			<td><input type="text" id="cc_email" name="cc_email" value="<?php echo empty($current_user->cc_email) ? esc_attr($current_user->user_email) : esc_attr($current_user->cc_email); ?>" class="required email" /></td>
		</tr>
		<tr>
			<td><label for="first-name"><?php _e( 'First Name', $this->text_domain ); ?>:</label></td>
			<td><input type="text" id="first-name" name="cc_firstname" value="<?php echo empty($current_user->cc_firstname) ? esc_attr($current_user->first_name) : esc_attr($current_user->cc_firstname); ?>" class="required"  /></td>
		</tr>
		<tr>
			<td><label for="last-name"><?php _e( 'Last Name', $this->text_domain ); ?>:</label></td>
			<td><input type="text" id="last-name" name="cc_lastname" value="<?php echo empty($current_user->cc_lastname) ? esc_attr($current_user->last_name) : esc_attr($current_user->cc_lastname); ?>" class="required"  /></td>
		</tr>
		<tr>
			<td><label for="street"><?php _e( 'Street', $this->text_domain ); ?>:</label></td>
			<td><input type="text" id="street" name="cc_street" value="<?php echo empty($current_user->cc_street) ? '' : esc_attr($current_user->cc_street); ?>" class="required" /></td>
		</tr>
		<tr>
			<td><label for="city"><?php _e( 'City', $this->text_domain ); ?>:</label></td>
			<td><input type="text" id="city" name="cc_city" value="<?php echo empty($current_user->cc_city) ? '' : esc_attr($current_user->cc_city); ?>" class="required" /></td>
		</tr>
		<tr>
			<td><label for="state"><?php _e( 'State', $this->text_domain ); ?>:</label></td>
			<td><input type="text" id="state" name="cc_state" value="<?php echo empty($current_user->cc_state) ? '' : esc_attr($current_user->cc_state); ?>" class="required" /></td>
		</tr>
		<tr>
			<td><label for="zip"><?php _e( 'Postal Code', $this->text_domain ); ?>:</label></td>
			<td><input type="text" id="zip" name="cc_zip" value="<?php echo empty($current_user->cc_zip) ? '' : esc_attr($current_user->cc_zip); ?>" class="required" /></td>
		</tr>
		<tr>
			<td><label for="country"><?php _e( 'Country', $this->text_domain ); ?>:</label></td>
			<td>
				<select id="country" name="cc_country_code"  class="required">
					<?php foreach ( $countries as $key => $value ) : ?>
					<option value="<?php echo $key; ?>" <?php selected( ! empty( $current_user->cc_country_code ) && $key == $current_user->cc_country_code ); ?>  ><?php echo $value; ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>

		<?php if(! $this->use_free): ?>

		<tr>
			<td><?php _e( 'Total Amount', $this->text_domain ); ?>:</td>
			<td>
				<strong><?php echo $_SESSION['cost']; ?> <?php echo (empty($options['payment_types']['paypal']['currency']) ) ? 'USD' : $options['payment_types']['paypal']['currency']; ?></strong>
				<input type="hidden" name="total_amount" value="<?php echo $_SESSION['cost']; ?>" />
			</td>
		</tr>

		<tr>
			<td><label for="cc_type"><?php _e( 'Credit Card Type', $this->text_domain ); ?>:</label></td>
			<td>
				<select name="cc_type">
					<option><?php _e( 'Visa', $this->text_domain ); ?></option>
					<option><?php _e( 'MasterCard', $this->text_domain ); ?></option>
					<option><?php _e( 'Amex', $this->text_domain ); ?></option>
					<option><?php _e( 'Discover', $this->text_domain ); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td><label for="cc_number"><?php _e( 'Credit Card Number', $this->text_domain ); ?>:</label></td>
			<td><input type="text" name="cc_number" class="required creditcard"/></td>
		</tr>
		<tr>
			<td><label for="exp_date"><?php _e( 'Expiration Date', $this->text_domain ); ?>:</label></td>
			<td>
				<select name="exp_date_month" id="exp_date" class="required" >
					<option value="01"><?php _e('01 Jan', $this->text_domain); ?></option>
					<option value="02"><?php _e('02 Feb', $this->text_domain); ?></option>
					<option value="03"><?php _e('03 Mar', $this->text_domain); ?></option>
					<option value="04"><?php _e('04 Apr', $this->text_domain); ?></option>
					<option value="05"><?php _e('05 May', $this->text_domain); ?></option>
					<option value="06"><?php _e('06 Jun', $this->text_domain); ?></option>
					<option value="07"><?php _e('07 Jul', $this->text_domain); ?></option>
					<option value="08"><?php _e('08 Aug', $this->text_domain); ?></option>
					<option value="09"><?php _e('09 Sep', $this->text_domain); ?></option>
					<option value="10"><?php _e('10 Oct', $this->text_domain); ?></option>
					<option value="11"><?php _e('11 Nov', $this->text_domain); ?></option>
					<option value="12"><?php _e('12 Dec', $this->text_domain); ?></option>
				</select>

				<select name="exp_date_year" class="required" >
					<?php for ( $i = date( 'Y' ); $i <= date( 'Y' ) + 10; $i++ ) { ?>
					<option value="<?php echo $i; ?>"><?php echo $i; ?></option>
					<?php } ?>
				</select>
			</td>
		</tr>
		<!--
		<tr>
		<td><label for="exp_date"><?php _e( 'Expiration Date (mm/yy)', $this->text_domain ); ?>:</label></td>
		<td><input type="text" name="exp_date" class="required" /></td>
		</tr>
		-->
		<tr>
			<td><label for="cvv2"><?php _e( 'CVV2', $this->text_domain ); ?>:</label></td>
			<td><input type="text" name="cvv2" class="required" /></td>
		</tr>
		<?php endif; ?>

	</table>

	<div class="clear"></div>
	<div class="submit">
		<input type="submit" name="direct_payment_submit" value="Continue" />
	</div>

</form>
<!-- End CC Details -->




<?php elseif ( $step == 'confirm_payment' ): ?>
<!-- Confirm -->
<form action="" method="post" class="checkout">
	<?php

	unset($_POST['direct_payment_submit']); //don't pass it again

	$cc = $_SESSION['CC'];

	foreach($cc as $key => $value) :
	?>
	<input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value;?>" />
	<?php endforeach; ?>

	<input type="hidden" name="billing_type" value="<?php echo $_SESSION['billing_type']; ?>" />
	<input type="hidden" name="credits" value="<?php echo (empty($_SESSION['credits']) ) ? 0 : $_SESSION['credits']; ?>" />


	<strong><?php _e( 'Confirm Payment', $this->text_domain ); ?></strong>
	<table>
		
		<?php if( !empty($cc['cc_email']) ): ?>
		<tr>
			<td><label><?php _e( 'Email Address', $this->text_domain ); ?>:</label></td>
			<td><?php echo $cc['cc_email']; ?></td>
		</tr>
		<?php endif; ?>
		
		<?php if( !empty($cc['cc_firstname']) ): ?>
		<tr>
			<td><label><?php _e( 'Name', $this->text_domain ); ?>:</label></td>
			<td><?php echo $cc['cc_firstname']; ?> <?php echo $cc['cc_lastname']; ?></td>
		</tr>
		<?php endif; ?>

		<?php if( !empty($cc['cc_street']) ): ?>
		<tr>
			<td><label><?php _e( 'Address', $this->text_domain ); ?>:</label></td>
			<td>
				<?php echo trim( sprintf( __ ( '%1$s, %2$s, %3$s %4$s %5$s', $this->text_domain), $cc['cc_street'], $cc['cc_city'], $cc['cc_state'], $cc['cc_zip'], $cc['cc_country_code']), ', ') ; ?>
			</td>
		</tr>
		<?php endif; ?>

		<?php if ( $_SESSION['billing_type'] == 'recurring' ): ?>
		<tr>
			<td><label><?php _e( 'Billing Agreement', $this->text_domain ); ?>:</label></td>
			<td><?php echo $_SESSION['billing_agreement']; ?></td>
		</tr>

		<?php endif; ?>
		<tr>
			<td><label><?php _e('Total Amount', $this->text_domain); ?>:</label></td>
			<td>
				<strong><?php echo $cc['total_amount']; ?> <?php echo (empty($cc['currency_code']) ) ? 'USD' : $cc['currency_code']; ?></strong>
			</td>
		</tr>

	</table>

	<div class="submit">
		<input type="submit" name="confirm_payment_submit" value="Confirm Payment" />
	</div>

</form>
<!--End Confirm-->






<?php elseif ( $step == 'api_call_error' ): ?>
<!--Begin Call Error -->

<ul>
	<li><?php echo $error['error_call'] . ' API call failed.'; ?></li>
	<li><?php echo 'Detailed Error Message: ' . $error['error_long_msg']; ?></li>
	<li><?php echo 'Short Error Message: '    . $error['error_short_msg']; ?></li>
	<li><?php echo 'Error Code: '             . $error['error_code']; ?></li>
	<li><?php echo 'Error Severity Code: '    . $error['error_severity_code']; ?></li>
</ul>
<!-- End Call Error-->








<?php /* Free Success */ ?>
<?php elseif ( $step == 'free_success' ): ?>

<div class="dp-submit-txt"><?php _e( 'The registration is completed successfully!', $this->text_domain ); ?></div>
<span class="dp-submit-txt"><?php _e( 'You can go to your profile and review/change your personal information, or you can go straight to the classifieds submission page.', $this->text_domain ); ?></span>
<br />

<?php echo do_shortcode('[cf_my_classifieds_btn text="' . __('Proceed to your Classifieds', $this->text_domain) . '" view="always"]'); ?>


<form id="go-to-profile-su" action="#" method="post">
	<input type="submit" name="redirect_profile" value="Go To Profile" />
</form>
<br class="clear" />


<?php /* Recurring payment */ ?>
<?php elseif ( $step == 'recurring_payment' ): ?>

<?php $transaction_details = get_query_var('checkout_transaction_details'); ?>

<form action="" method="post" class="checkout">
	<?php

	unset($_POST['payment_method_submit']); //don't pass it again

	$cc = $_SESSION['CC'];
	foreach($cc as $key => $value) :
	?>
	<input type="hidden" name="<?php echo $key; ?>" value="<?php echo $value;?>" />
	<?php endforeach; ?>

	<input type="hidden" name="billing_type" value="<?php echo $_SESSION['billing_type']; ?>" />
	<input type="hidden" name="credits" value="<?php echo empty($_SESSION['credits']) ? 0 : $_SESSION['credits']; ?>" />


	<strong><?php _e( 'Confirm Payment', $this->text_domain ); ?></strong>
	<table>
		<?php if( !empty($cc['cc_email']) ): ?>
		<tr>
			<td><label><?php _e( 'Email Address', $this->text_domain ); ?>:</label></td>
			<td><?php echo empty($cc['cc_email']) ? $current_user->user_email : $cc['cc_email']; ?></td>
		</tr>
		<?php endif; ?>
		
		<?php if( !empty($cc['cc_firstname']) ): ?>
		<tr>
			<td><label><?php _e( 'Name', $this->text_domain ); ?>:</label></td>
			<td><?php echo empty($cc['cc_firstname']) ? $current_user->first_name : $cc['cc_firstname']; ?> <?php echo empty($cc['cc_lastname']) ? $current_user->last_name : $cc['cc_lastname']; ?></td>
		</tr>
		<?php endif; ?>

		<?php if( !empty($cc['cc_street']) ): ?>
		<tr>
			<td><label><?php _e( 'Address', $this->text_domain ); ?>:</label></td>
			<td>
				<?php echo trim( sprintf( __ ( '%1$s, %2$s, %3$s %4$s %5$s', $this->text_domain), $cc['cc_street'], $cc['cc_city'], $cc['cc_state'], $cc['cc_zip'], $cc['cc_country_code']), ', ') ; ?>
			</td>
		</tr>
		<?php endif; ?>

		<tr>
			<td><label><?php _e('Total Amount', $this->text_domain); ?>:</label></td>
			<td>
				<strong><?php echo $cc['total_amount']; ?> <?php echo (empty($cc['currency_code']) ) ? 'USD' : $cc['currency_code']; ?></strong>
			</td>
		</tr>
	</table>
	<div class="submit">
		<input type="submit" name="recurring_submit" value="<?php _e( 'Confirm data', $this->text_domain ); ?>" />
	</div>

</form>





<?php elseif ( $step == 'success' ): ?>
<!-- Begin Success -->
<div class="dp-thank-you"><?php _e( 'Thank you for your business. Transaction processed successfully!', $this->text_domain ); ?></div>
<span class="dp-submit-txt"><?php _e( 'You can go to your profile and review/change your personal information. You can also go straight to classifieds submission page.', $this->text_domain ); ?></span>
<br /><br />

<?php echo do_shortcode('[cf_my_classifieds_btn text="' . __('Proceed to your Classifieds', $this->text_domain) . '" view="always"]'); ?>

<!-- End Success -->
<?php endif; ?>
<div class="clear"></div><br />

<?php wp_link_pages( array( 'before' => '<div class="page-link">' . __( 'Pages:', 'twentyten' ), 'after' => '</div>' ) ); ?>
<?php edit_post_link( __( 'Edit', 'twentyten' ), '<span class="edit-link">', '</span>' ); ?>

<script type="text/javascript">jQuery('.checkout').validate();</script>

