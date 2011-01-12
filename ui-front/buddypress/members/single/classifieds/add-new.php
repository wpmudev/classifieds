<?php if (!defined('ABSPATH')) die('No direct access allowed!'); ?>

<div class="profile">
    
    <form class="standard-form base" method="post" action="">

        <h4><?php _e( 'Create New Ad', $this->text_domain ); ?></h4>
        <ul class="button-nav">
            <li class="current"><a href="http://wordpress.loc/members/admin/profile/edit/group/1">Base</a></li>
            <li><a href="http://wordpress.loc/members/admin/profile/edit/group/1">No base</a></li>
        </ul>
        <div class="clear"></div>
        
        <div class="editfield">
            <label for="title"><?php _e( 'Title', $this->text_domain ); ?></label>
            <input type="text" value="" id="title" name="title">
            <p class="description"><?php _e( 'Enter title here.', $this->text_domain ); ?></p>
        </div>

        <div class="editfield alt">
            <label for="description"><?php _e( 'Description', $this->text_domain ); ?></label>
            <textarea id="description" name="description" cols="40" rows="5"></textarea>
            <p class="description"><?php _e( 'The main description of your ad.', $this->text_domain ); ?></p>
        </div>

        <div class="editfield">
            <label for="duration"><?php _e( 'Category', $this->text_domain ); ?></label>
            <select id="duration" name="duration">
                <option value="">--------</option>
                <optgroup label="Directory">
                    <option value="1"><?php _e( 'Arts', $this->text_domain ); ?></option>
                    <option value="1"><?php _e( 'News', $this->text_domain ); ?></option>
                </optgroup>
            </select>
            <p class="description"><?php _e( 'Select category for your ad.', $this->text_domain ); ?></p>
        </div>

        <div class="editfield">
            <div class="radio">
                <span class="label"><?php _e( 'Ad Status' );  ?></span>
                <div id="status-box">
                    <label><input type="radio" value="published" name="status" checked="checked"><?php _e( 'Published', $this->text_domain ); ?></label>
                    <label><input type="radio" value="draft" name="status"><?php _e( 'Draft', $this->text_domain ); ?></label>
                </div>
                <a href="javascript:clear( 'field_5' );" class="clear-value">Clear</a>
            </div>
            <p class="description"><?php _e( 'Check a status for your Ad.', $this->text_domain ); ?></p>
        </div>

        <div class="editfield">
            <label for="price"><?php _e( 'Price', $this->text_domain ); ?></label>
            <input type="text" value="" id="price" name="price">
            <p class="description"><?php _e( 'The price of the product or service you promote here.', $this->text_domain ); ?></p>
        </div>

        <div class="editfield">
            <label for="duration"><?php _e( 'Duration', $this->text_domain ); ?></label>
            <select id="duration" name="duration">
                <option value="">--------</option>
                <option value="1"><?php _e( '1 Week', $this->text_domain ); ?></option>
                <option value="2"><?php _e( '2 Weeks', $this->text_domain ); ?></option>
                <option value="3"><?php _e( '3 Weeks', $this->text_domain ); ?></option>
                <option value="4"><?php _e( '4 Weeks', $this->text_domain ); ?></option>
            </select>
            <p class="description"><?php _e( 'The duration of your ad until it expires.', $this->text_domain ); ?></p>
        </div>

        <div class="editfield">
            <label for="duration"><?php _e( 'Select Featured Image', $this->text_domain ); ?></label>
            <p id="featured-image">
                <input type="file" id="image" name="image">
                <input type="hidden" value="featured-image" id="action" name="action">
            </p>
        </div>

        <div class="submit">
            <input type="submit" value="Save Changes " name="save">
        </div>
        
    </form>
    
</div>