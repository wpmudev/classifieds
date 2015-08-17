<?php if (!defined('ABSPATH')) die('No direct access allowed!'); ?>

<div class="wrap">

	<?php $this->render_admin( 'navigation', array( 'page' => 'classifieds_settings', 'tab' => 'shortcodes' ) ); ?>
	<?php $this->render_admin( 'message' ); ?>

	<h1><?php _e( 'Classifieds Shortcodes', $this->text_domain ); ?></h1>

	<div class="postbox">
		<h3 class='hndle'><span><?php _e( 'Classifieds Shortcodes', $this->text_domain ) ?></span></h3>
		<div class="inside">
			<p>
				<?php _e( 'Shortcodes allow you to include dynamic store content in posts and pages on your site. Simply type or paste them into your post or page content where you would like them to appear. Optional attributes can be added in a format like <em>[shortcode attr1="value" attr2="value"]</em>.', $this->text_domain ) ?>
			</p>
			<p>
				<?php _e( 'Attributes: ("|" means use one OR the other. ie style="grid" or style="list" not style="grid | list")', $this->text_domain); ?>
				<br /><?php _e( 'text = <em>Text to display on a button</em>', $this->text_domain ) ?>
				<br /><?php _e( 'view = <em>Whether the button is visible when loggedin, loggedout, or both</em>', $this->text_domain ) ?>
				<br /><?php _e( 'redirect = <em>On the Logout button, what page to go to after logout</em>', $this->text_domain ) ?>
				<br /><?php _e( 'ccats = <em>A comma separated list of classifieds_categories ids to display</em>', $this->text_domain ) ?>
			</p>
			<table class="form-table">
				<tr>
					<th scope="row"><?php _e( 'List of Categories:', $this->text_domain ) ?></th>
					<td>
						<code><strong>[cf_list_categories style="grid | list" ccats="1,2,3" ]</strong></code>
						<br /><span class="description"><?php _e( 'Displays a list of Classifieds categories.', $this->text_domain ) ?></span>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'Classifieds Button:', $this->text_domain ) ?></th>
					<td>
						<code><strong>[cf_classifieds_btn text="<?php _e('Classifieds', $this->text_domain);?>" view="loggedin | loggedout | both"]</strong></code> or
						<br /><code><strong>[cf_classifieds_btn view="loggedin | loggedout | both"]&lt;img src="<?php _e('someimage.jpg', $this->text_domain); ?>" /&gt;<?php _e('Classifieds', $this->text_domain);?>[/cf_classifieds_btn]</strong></code>
						<br /><span class="description"><?php _e( 'Links to the Classifieds List Page. Generates a &lt;button&gt; &lt;/button&gt; with the contents you define.', $this->text_domain ) ?></span>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'My Classifieds Button:', $this->text_domain ) ?></th>
					<td>
						<code><strong>[cf_my_classifieds_btn text="<?php _e('My Classifieds', $this->text_domain);?>" view="loggedin | loggedout | both"]</strong></code> or
						<br /><code><strong>[cf_my_classifieds_btn view="loggedin | loggedout | both"]&lt;img src="<?php _e('someimage.jpg', $this->text_domain); ?>" /&gt;<?php _e('My Classifieds', $this->text_domain);?>[/cf_my_classifieds_btn]</strong></code>
						<br /><span class="description"><?php _e( 'Links to the My Classifieds Page. Generates a &lt;button&gt; &lt;/button&gt; with the contents you define.', $this->text_domain ) ?></span>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'My Classified Credits Button:', $this->text_domain ) ?></th>
					<td>
						<code><strong>[cf_my_credits_btn text="<?php _e('My Classified Credits', $this->text_domain);?>" view="loggedin | loggedout | both"]</strong></code> or
						<br /><code><strong>[cf_my_credits_btn view="loggedin | loggedout | both"]&lt;img src="<?php _e('someimage.jpg', $this->text_domain); ?>" /&gt;<?php _e('Edit Classified', $this->text_domain);?>[/cf_my_credits_btn]</strong></code>
						<br /><span class="description"><?php _e( 'Links to the Classifieds Credit management page. Generates a &lt;button&gt; &lt;/button&gt; with the contents you define.', $this->text_domain ) ?></span>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'Classifieds Checkout Button:', $this->text_domain ) ?></th>
					<td>
						<code><strong>[cf_checkout_btn text="<?php _e('Checkout', $this->text_domain);?>" view="loggedin | loggedout | both"]</strong></code> or
						<br /><code><strong>[cf_checkout_btn view="loggedin | loggedout | both"]&lt;img src="<?php _e('someimage.jpg', $this->text_domain); ?>" /&gt;<?php _e('Signup', $this->text_domain);?>[/cf_checkout_btn]</strong></code>
						<br /><span class="description"><?php _e( 'Links to the Classifeds Checkout Page. Generates a &lt;button&gt; &lt;/button&gt; with the contents you define.', $this->text_domain ) ?></span>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'Add Classified Button:', $this->text_domain ) ?></th>
					<td>
						<code><strong>[cf_add_classified_btn text="<?php _e('Add Classified', $this->text_domain);?>" view="loggedin | loggedout | both"]</strong></code> or
						<br /><code><strong>[cf_add_classified_btn view="loggedin | loggedout | both"]&lt;img src="<?php _e('someimage.jpg', $this->text_domain); ?>" /&gt;<?php _e('Add Classified', $this->text_domain);?>[/cf_add_classified_btn]</strong></code>
						<br /><span class="description"><?php _e( 'Links to the Add Classifieds Page. Generates a &lt;button&gt; &lt;/button&gt; with the contents you define.', $this->text_domain ) ?></span>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'Edit Classified Button:', $this->text_domain ) ?></th>
					<td>
						<code><strong>[cf_edit_classified_btn text="<?php _e('Edit Classified', $this->text_domain);?>" post="post_id" view="loggedin | loggedout | both"]</strong></code> or
						<br /><code><strong>[cf_edit_classified_btn post="post_id" view="loggedin | loggedout | both"]&lt;img src="<?php _e('someimage.jpg', $this->text_domain); ?>" /&gt;<?php _e('Edit Classified', $this->text_domain);?>[/cf_edit_classified_btn]</strong></code>
						<br /><span class="description"><?php _e( 'Links to the Edit Classifieds Page. Generates a &lt;button&gt; &lt;/button&gt; with the contents you define.', $this->text_domain ) ?></span>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'Profile Button:', $this->text_domain ) ?></th>
					<td>
						<code><strong>[cf_profile_btn text="<?php _e('Go to Profile', $this->text_domain);?>" view="loggedin | loggedout | both"]</strong></code> or
						<br /><code><strong>[cf_profile_btn view="loggedin | loggedout | both"]&lt;img src="<?php _e('someimage.jpg', $this->text_domain); ?>" /&gt;<?php _e('Go to Profile', $this->text_domain);?>[/cf_profile_btn]</strong></code>
						<br /><span class="description"><?php _e( 'Links to the Profile Page. Generates a &lt;button&gt; &lt;/button&gt; with the contents you define.', $this->text_domain ) ?></span>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'Signin Button:', $this->text_domain ) ?></th>
					<td>
						<code><strong>[cf_signin_btn text="<?php _e('Signin', $this->text_domain);?>" view="loggedin | loggedout | both"]</strong></code> or
						<br /><code><strong>[cf_signin_btn view="loggedin | loggedout | both"]&lt;img src="<?php _e('someimage.jpg', $this->text_domain); ?>" /&gt;<?php _e('Signin', $this->text_domain);?>[/cf_signin_btn]</strong></code>
						<br /><span class="description"><?php _e( 'Links to the Signin Page. Generates a &lt;button&gt; &lt;/button&gt; with the contents you define.', $this->text_domain ) ?></span>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'Checkout Button:', $this->text_domain ) ?></th>
					<td>
						<code><strong>[cf_checkout_btn text="<?php _e('Checkout', $this->text_domain);?>" view="loggedin | loggedout | both"]</strong></code> or
						<br /><code><strong>[cf_checkout_btn view="loggedin | loggedout | both"]&lt;img src="<?php _e('someimage.jpg', $this->text_domain); ?>" /&gt;<?php _e('Signup', $this->text_domain);?>[/cf_checkout_btn]</strong></code>
						<br /><span class="description"><?php _e( 'Links to the Checkout Page. Generates a &lt;button&gt; &lt;/button&gt; with the contents you define.', $this->text_domain ) ?></span>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'Logout Button:', $this->text_domain ) ?></th>
					<td>
						<code><strong>[cf_logout_btn text="<?php _e('Logout', $this->text_domain);?>"  view="loggedin | loggedout | both" redirect="http://someurl"]</strong></code> or
						<br /><code><strong>[cf_logout_btn  view="loggedin | loggedout | always" redirect="http://someurl"]&lt;img src="<?php _e('someimage.jpg', $this->text_domain); ?>" /&gt;<?php _e('Logout', $this->text_domain);?>[/cf_logout_btn]</strong></code>
						<br /><span class="description"><?php _e( 'Links to the Logout Page. Generates a &lt;button&gt; &lt;/button&gt; with the contents you define. The "redirect" attribute is the url to go to after logging out.', $this->text_domain ) ?></span>
					</td>
				</tr>
			</table>
		</div>
	</div>

</div>
