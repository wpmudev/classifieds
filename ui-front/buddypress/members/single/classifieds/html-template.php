<?php if (!defined('ABSPATH')) die('No direct access allowed!'); ?>

<div class="profile">
    
    <form class="standard-form base" id="profile-edit-form" method="post" action="http://wordpress.loc/members/admin/profile/edit/group/1/">

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
            <p class="description">Radio buttons baby.</p>
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
            <label for="duration"><?php _e( 'Upload Featured Image', $this->text_domain ); ?></label>
            <p id="featured-image">
                <input type="file" id="image" name="image">
                <input type="submit" value="<?php _e( 'Upload Image', $this->text_domain ); ?>" id="upload" name="upload">
                <input type="hidden" value="featured-image" id="action" name="action">
            </p>
        </div>

        <div class="editfield alt">
            <div class="datebox">
                <label for="field_4_day">Date selector. </label>

                <select id="field_4_day" name="field_4_day">
                    <option value="">--</option><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10">10</option><option value="11">11</option><option value="12">12</option><option value="13">13</option><option value="14">14</option><option value="15">15</option><option value="16">16</option><option value="17">17</option><option value="18">18</option><option value="19">19</option><option value="20">20</option><option value="21">21</option><option value="22">22</option><option value="23">23</option><option value="24">24</option><option value="25">25</option><option value="26">26</option><option value="27">27</option><option value="28">28</option><option value="29">29</option><option value="30">30</option><option value="31">31</option>						</select>

                <select id="field_4_month" name="field_4_month">
                    <option value="">------</option><option value="January">January</option><option value="February">February</option><option value="March">March</option><option value="April">April</option><option value="May">May</option><option value="June">June</option><option value="July">July</option><option value="August">August</option><option value="September">September</option><option value="October">October</option><option value="November">November</option><option value="December">December</option>						</select>

                <select id="field_4_year" name="field_4_year">
                    <option value="">----</option><option value="2010">2010</option><option value="2009">2009</option><option value="2008">2008</option><option value="2007">2007</option><option value="2006">2006</option><option value="2005">2005</option><option value="2004">2004</option><option value="2003">2003</option><option value="2002">2002</option><option value="2001">2001</option><option value="2000">2000</option><option value="1999">1999</option><option value="1998">1998</option><option value="1997">1997</option><option value="1996">1996</option><option value="1995">1995</option><option value="1994">1994</option><option value="1993">1993</option><option value="1992">1992</option><option value="1991">1991</option><option value="1990">1990</option><option value="1989">1989</option><option value="1988">1988</option><option value="1987">1987</option><option value="1986">1986</option><option value="1985">1985</option><option value="1984">1984</option><option value="1983">1983</option><option value="1982">1982</option><option value="1981">1981</option><option value="1980">1980</option><option value="1979">1979</option><option value="1978">1978</option><option value="1977">1977</option><option value="1976">1976</option><option value="1975">1975</option><option value="1974">1974</option><option value="1973">1973</option><option value="1972">1972</option><option value="1971">1971</option><option value="1970">1970</option><option value="1969">1969</option><option value="1968">1968</option><option value="1967">1967</option><option value="1966">1966</option><option value="1965">1965</option><option value="1964">1964</option><option value="1963">1963</option><option value="1962">1962</option><option value="1961">1961</option><option value="1960">1960</option><option value="1959">1959</option><option value="1958">1958</option><option value="1957">1957</option><option value="1956">1956</option><option value="1955">1955</option><option value="1954">1954</option><option value="1953">1953</option><option value="1952">1952</option><option value="1951">1951</option><option value="1950">1950</option><option value="1949">1949</option><option value="1948">1948</option><option value="1947">1947</option><option value="1946">1946</option><option value="1945">1945</option><option value="1944">1944</option><option value="1943">1943</option><option value="1942">1942</option><option value="1941">1941</option><option value="1940">1940</option><option value="1939">1939</option><option value="1938">1938</option><option value="1937">1937</option><option value="1936">1936</option><option value="1935">1935</option><option value="1934">1934</option><option value="1933">1933</option><option value="1932">1932</option><option value="1931">1931</option><option value="1930">1930</option><option value="1929">1929</option><option value="1928">1928</option><option value="1927">1927</option><option value="1926">1926</option><option value="1925">1925</option><option value="1924">1924</option><option value="1923">1923</option><option value="1922">1922</option><option value="1921">1921</option><option value="1920">1920</option><option value="1919">1919</option><option value="1918">1918</option><option value="1917">1917</option><option value="1916">1916</option><option value="1915">1915</option><option value="1914">1914</option><option value="1913">1913</option><option value="1912">1912</option><option value="1911">1911</option><option value="1910">1910</option><option value="1909">1909</option><option value="1908">1908</option><option value="1907">1907</option><option value="1906">1906</option><option value="1905">1905</option><option value="1904">1904</option><option value="1903">1903</option><option value="1902">1902</option><option value="1901">1901</option><option value="1900">1900</option>						</select>
            </div>
            <p class="description">This is a date-selector.</p>
        </div>

        <div class="editfield">
            <label for="field_12[]">Multi select box. </label>
            <select multiple="multiple" id="field_12[]" name="field_12[]">
            <option value="Alfonso">Alfonso</option><option value="Another Alfonso" selected="selected">Another Alfonso</option><option value="And a third Alfonso">And a third Alfonso</option></select>
            <a href="javascript:clear( 'field_12[]' );" class="clear-value">Clear</a>
            <p class="description">Multi select box baby.</p>
        </div>

        <div class="editfield alt">
            <div class="checkbox">
                <span class="label">Checkboxes </span>
                <label><input type="checkbox" value="Super serious option" id="field_17_0" name="field_16[]"> Super serious option</label><label>
                <input type="checkbox" value="Super mega serious option" id="field_18_1" name="field_16[]"> Super mega serious option</label><label><input type="checkbox" value="Shit, this option is serious" id="field_19_2" name="field_16[]"> Shit, this option is serious</label>
            </div>
            <p class="description">Finaly the checkboxes.</p>
        </div>

        <div class="submit">
            <input type="submit" value="Save Changes " id="profile-group-edit-submit" name="profile-group-edit-submit">
        </div>

        <input type="hidden" value="1,2,3,4,5,9,12,16" id="field_ids" name="field_ids">
        <input type="hidden" value="9b0f6ad1f7" name="_wpnonce" id="_wpnonce"><input type="hidden" value="/members/admin/profile/edit/group/1" name="_wp_http_referer">

    </form>
    
</div>