<?php

$templates = array(
	array(
		'id'      => 'contact-form',
		'name'    => 'Contact Form',
		
		'icon'    => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#1f1f1f"><path d="M172.31-180Q142-180 121-201q-21-21-21-51.31v-455.38Q100-738 121-759q21-21 51.31-21h615.38Q818-780 839-759q21 21 21 51.31v455.38Q860-222 839-201q-21 21-51.31 21H172.31ZM800-662.31 499.46-469.92q-4.61 2.61-9.54 4.11-4.92 1.5-9.92 1.5t-9.92-1.5q-4.93-1.5-9.54-4.11L160-662.31v410q0 5.39 3.46 8.85t8.85 3.46h615.38q5.39 0 8.85-3.46t3.46-8.85v-410ZM480-520l313.85-200h-627.7L480-520ZM160-662.31v9.23-45.73 1.19V-720v22.38-1.27V-653.08v-9.23V-240v-422.31Z"/></svg>',

		'desc'    => 'A simple contact form with fields for name, email, subject, and message.',
		'content' => '<!-- wp:wpzoom-forms/form -->
<div class="wp-block-wpzoom-forms-form"><!-- wp:wpzoom-forms/text-name-field {"id":"input_ae87379b","name":"Name"} -->
<label for="input_ae87379b"><span>Name</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_ae87379b" id="input_ae87379b" placeholder="" required class="wp-block-wpzoom-forms-text-name-field"/>
<!-- /wp:wpzoom-forms/text-name-field -->

<!-- wp:wpzoom-forms/text-email-field {"id":"input_8468ae36","name":"Email"} -->
<label for="input_8468ae36"><span>Email</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="email" name="input_8468ae36" id="input_8468ae36" placeholder="" required data-replyto="false" class="wp-block-wpzoom-forms-text-email-field"/>
<!-- /wp:wpzoom-forms/text-email-field -->

<!-- wp:wpzoom-forms/text-plain-field {"id":"input_85d61063","name":"Subject","label":"Subject","subject":true} -->
<label for="input_85d61063"><span>Subject</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_85d61063" id="input_85d61063" placeholder="" required data-subject="true" class="wp-block-wpzoom-forms-text-plain-field"/>
<!-- /wp:wpzoom-forms/text-plain-field -->

<!-- wp:wpzoom-forms/textarea-field {"id":"input_9d8d7aa6","name":"Message"} -->
<label for="input_9d8d7aa6"><span>Message</span></label><textarea name="input_9d8d7aa6" id="input_9d8d7aa6" cols="55" rows="10" placeholder="" class="wp-block-wpzoom-forms-textarea-field"></textarea>
<!-- /wp:wpzoom-forms/textarea-field -->

<!-- wp:columns {"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"30%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:30%"><!-- wp:wpzoom-forms/submit-field {"id":"input_submit"} -->
<input type="submit" id="input_submit" value="Submit" class="wp-block-wpzoom-forms-submit-field"/>
<!-- /wp:wpzoom-forms/submit-field --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"70%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:70%"><!-- wp:paragraph {"align":"right","style":{"typography":{"fontSize":16}}} -->
<p class="has-text-align-right" style="font-size:16px">Fields marked with <strong class="has-accent-color has-text-color">*</strong> are required.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:wpzoom-forms/form -->'
	),
	array(
		'id'      => 'advanced-form',
		'name'    => 'Contact Form (Detailed)',
		
		'icon'    => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#1f1f1f"><path d="M480-409.23q29.15 0 49.58-20.42Q550-450.08 550-479.23t-20.42-49.58q-20.43-20.42-49.58-20.42t-49.58 20.42Q410-508.38 410-479.23t20.42 49.58q20.43 20.42 49.58 20.42ZM330-261.54h300v-14.54q0-21.31-11.46-37.84-11.46-16.54-31.39-25.39-24.84-10.61-51.57-16.42-26.73-5.81-55.58-5.81-28.85 0-55.58 5.81-26.73 5.81-51.57 16.42-19.93 8.85-31.39 25.39Q330-297.39 330-276.08v14.54ZM707.69-100H252.31Q222-100 201-121q-21-21-21-51.31v-615.38Q180-818 201-839q21-21 51.31-21h269.3q14.47 0 27.81 5.62 13.35 5.61 23.19 15.46l186.31 186.31q9.85 9.84 15.46 23.19 5.62 13.34 5.62 27.81v429.3Q780-142 759-121q-21 21-51.31 21Zm0-60q4.62 0 8.46-3.85 3.85-3.84 3.85-8.46V-606L526-800H252.31q-4.62 0-8.46 3.85-3.85 3.84-3.85 8.46v615.38q0 4.62 3.85 8.46 3.84 3.85 8.46 3.85h455.38ZM240-160v-640V-160Z"/></svg>',

		'desc'    => 'An extended contact form that includes address and phone fields.',
		'content' => '<!-- wp:wpzoom-forms/form -->
<div class="wp-block-wpzoom-forms-form"><!-- wp:group -->
<div class="wp-block-group"><!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"width":"100%"} -->
<div class="wp-block-column" style="flex-basis:100%"><!-- wp:paragraph -->
<p><strong>Your Name</strong></p>
<!-- /wp:paragraph -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"top":"0","left":"var:preset|spacing|40"}}}} -->
<div class="wp-block-columns"><!-- wp:column {"width":"","style":{"spacing":{"padding":{"right":"var:preset|spacing|40"}}}} -->
<div class="wp-block-column" style="padding-right:var(--wp--preset--spacing--40)"><!-- wp:wpzoom-forms/text-name-field {"id":"input_name","name":"First","label":"First","style":{"spacing":{"margin":{"left":"0px"}}}} -->
<label for="input_name"><span>First</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_name" id="input_name" placeholder="" required class="wp-block-wpzoom-forms-text-name-field" style="margin-left:0px"/>
<!-- /wp:wpzoom-forms/text-name-field --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:wpzoom-forms/text-plain-field {"id":"input_44c5e8a3","name":"Last","label":"Last"} -->
<label for="input_44c5e8a3"><span>Last</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_44c5e8a3" id="input_44c5e8a3" placeholder="" required data-subject="false" class="wp-block-wpzoom-forms-text-plain-field"/>
<!-- /wp:wpzoom-forms/text-plain-field --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:wpzoom-forms/text-email-field {"id":"input_email","name":"Email","replyto":true} -->
<label for="input_email"><span>Email</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="email" name="input_email" id="input_email" placeholder="" required data-replyto="true" class="wp-block-wpzoom-forms-text-email-field"/>
<!-- /wp:wpzoom-forms/text-email-field -->

<!-- wp:wpzoom-forms/text-phone-field {"id":"input_1bb8d86c","name":"Phone number","label":"Phone number"} -->
<label for="input_1bb8d86c"><span>Phone number</span></label><input type="tel" name="input_1bb8d86c" id="input_1bb8d86c" placeholder="" class="wp-block-wpzoom-forms-text-phone-field"/>
<!-- /wp:wpzoom-forms/text-phone-field -->

<!-- wp:spacer {"height":"40px"} -->
<div style="height:40px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:paragraph -->
<p><strong>Your Address</strong></p>
<!-- /wp:paragraph -->

<!-- wp:wpzoom-forms/text-plain-field {"id":"input_63de4a79","name":"Street address","label":"Street address"} -->
<label for="input_63de4a79"><span>Street address</span></label><input type="text" name="input_63de4a79" id="input_63de4a79" placeholder="" data-subject="false" class="wp-block-wpzoom-forms-text-plain-field"/>
<!-- /wp:wpzoom-forms/text-plain-field -->

<!-- wp:wpzoom-forms/text-plain-field {"id":"input_c2f48087","name":"Address Line 2","label":"Address Line 2"} -->
<label for="input_c2f48087"><span>Address Line 2</span></label><input type="text" name="input_c2f48087" id="input_c2f48087" placeholder="" data-subject="false" class="wp-block-wpzoom-forms-text-plain-field"/>
<!-- /wp:wpzoom-forms/text-plain-field -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|small"}}}} -->
<div class="wp-block-columns"><!-- wp:column {"style":{"spacing":{"padding":{"right":"var:preset|spacing|40"}}}} -->
<div class="wp-block-column" style="padding-right:var(--wp--preset--spacing--40)"><!-- wp:wpzoom-forms/text-plain-field {"id":"input_aded2b39","name":"City","label":"City"} -->
<label for="input_aded2b39"><span>City</span></label><input type="text" name="input_aded2b39" id="input_aded2b39" placeholder="" data-subject="false" class="wp-block-wpzoom-forms-text-plain-field"/>
<!-- /wp:wpzoom-forms/text-plain-field --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:wpzoom-forms/text-plain-field {"id":"input_5d19b773","name":"ZIP Code","label":"ZIP Code"} -->
<label for="input_5d19b773"><span>ZIP Code</span></label><input type="text" name="input_5d19b773" id="input_5d19b773" placeholder="" data-subject="false" class="wp-block-wpzoom-forms-text-plain-field"/>
<!-- /wp:wpzoom-forms/text-plain-field --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:columns {"style":{"spacing":{"blockGap":{"left":"var:preset|spacing|small"}}}} -->
<div class="wp-block-columns"><!-- wp:column {"style":{"spacing":{"padding":{"right":"var:preset|spacing|40"}}}} -->
<div class="wp-block-column" style="padding-right:var(--wp--preset--spacing--40)"><!-- wp:wpzoom-forms/select-field {"id":"input_3c1eb2cc","name":"State","options":["Alabama","Alaska","Arizona","Arkansas","California","Colorado","Connecticut","Delaware","Florida","Georgia","Hawaii","Idaho","Illinois","Indiana","Iowa","Kansas","Kentucky","Louisiana","Maine","Maryland","Massachusetts","Michigan","Minnesota","Mississippi","Missouri","Montana","Nebraska","Nevada","New Hampshire","New Jersey","New Mexico","New York","North Carolina","North Dakota","Ohio","Oklahoma","Oregon","Pennsylvania","Rhode Island","South Carolina","South Dakota","Tennessee","Texas","Utah","Vermont","Virginia","Washington","West Virginia","Wisconsin","Wyoming"],"label":"State"} -->
<label for="input_3c1eb2cc"><span>State</span></label><select name="input_3c1eb2cc" id="input_3c1eb2cc" defaultvalue="Alabama" class="wp-block-wpzoom-forms-select-field"><option value="Alabama">Alabama</option><option value="Alaska">Alaska</option><option value="Arizona">Arizona</option><option value="Arkansas">Arkansas</option><option value="California">California</option><option value="Colorado">Colorado</option><option value="Connecticut">Connecticut</option><option value="Delaware">Delaware</option><option value="Florida">Florida</option><option value="Georgia">Georgia</option><option value="Hawaii">Hawaii</option><option value="Idaho">Idaho</option><option value="Illinois">Illinois</option><option value="Indiana">Indiana</option><option value="Iowa">Iowa</option><option value="Kansas">Kansas</option><option value="Kentucky">Kentucky</option><option value="Louisiana">Louisiana</option><option value="Maine">Maine</option><option value="Maryland">Maryland</option><option value="Massachusetts">Massachusetts</option><option value="Michigan">Michigan</option><option value="Minnesota">Minnesota</option><option value="Mississippi">Mississippi</option><option value="Missouri">Missouri</option><option value="Montana">Montana</option><option value="Nebraska">Nebraska</option><option value="Nevada">Nevada</option><option value="New Hampshire">New Hampshire</option><option value="New Jersey">New Jersey</option><option value="New Mexico">New Mexico</option><option value="New York">New York</option><option value="North Carolina">North Carolina</option><option value="North Dakota">North Dakota</option><option value="Ohio">Ohio</option><option value="Oklahoma">Oklahoma</option><option value="Oregon">Oregon</option><option value="Pennsylvania">Pennsylvania</option><option value="Rhode Island">Rhode Island</option><option value="South Carolina">South Carolina</option><option value="South Dakota">South Dakota</option><option value="Tennessee">Tennessee</option><option value="Texas">Texas</option><option value="Utah">Utah</option><option value="Vermont">Vermont</option><option value="Virginia">Virginia</option><option value="Washington">Washington</option><option value="West Virginia">West Virginia</option><option value="Wisconsin">Wisconsin</option><option value="Wyoming">Wyoming</option></select>
<!-- /wp:wpzoom-forms/select-field --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:wpzoom-forms/select-field {"id":"input_e6c773f3","name":"Country","options":["Afghanistan","Albania","Algeria","Andorra","Angola","Antigua \u0026 Deps","Argentina","Armenia","Australia","Austria","Azerbaijan","Bahamas","Bahrain","Bangladesh","Barbados","Belarus","Belgium","Belize","Benin","Bhutan","Bolivia","Bosnia Herzegovina","Botswana","Brazil","Brunei","Bulgaria","Burkina","Burundi","Cambodia","Cameroon","Canada","Cape Verde","Central African Rep","Chad","Chile","China","Colombia","Comoros","Congo","Congo (Democratic Rep)","Costa Rica","Croatia","Cuba","Cyprus","Czech Republic","Denmark","Djibouti","Dominica","Dominican Republic","East Timor","Ecuador","Egypt","El Salvador","Equatorial Guinea","Eritrea","Estonia","Ethiopia","Fiji","Finland","France","Gabon","Gambia","Georgia","Germany","Ghana","Greece","Grenada","Guatemala","Guinea","Guinea-Bissau","Guyana","Haiti","Honduras","Hungary","Iceland","India","Indonesia","Iran","Iraq","Ireland {Republic}","Israel","Italy","Ivory Coast","Jamaica","Japan","Jordan","Kazakhstan","Kenya","Kiribati","Korea North","Korea South","Kosovo","Kuwait","Kyrgyzstan","Laos","Latvia","Lebanon","Lesotho","Liberia","Libya","Liechtenstein","Lithuania","Luxembourg","Macedonia","Madagascar","Malawi","Malaysia","Maldives","Mali","Malta","Marshall Islands","Mauritania","Mauritius","Mexico","Micronesia","Moldova","Monaco","Mongolia","Montenegro","Morocco","Mozambique","Myanmar, {Burma}","Namibia","Nauru","Nepal","Netherlands","New Zealand","Nicaragua","Niger","Nigeria","Norway","Oman","Pakistan","Palau","Panama","Papua New Guinea","Paraguay","Peru","Philippines","Poland","Portugal","Qatar","Romania","Russian Federation","Rwanda","St Kitts \u0026 Nevis","St Lucia","Saint Vincent \u0026 the Grenadines","Samoa","San Marino","Sao Tome \u0026 Principe","Saudi Arabia","Senegal","Serbia","Seychelles","Sierra Leone","Singapore","Slovakia","Slovenia","Solomon Islands","Somalia","South Africa","South Sudan","Spain","Sri Lanka","Sudan","Suriname","Swaziland","Sweden","Switzerland","Syria","Taiwan","Tajikistan","Tanzania","Thailand","Togo","Tonga","Trinidad \u0026 Tobago","Tunisia","Turkey","Turkmenistan","Tuvalu","Uganda","Ukraine","United Arab Emirates","United Kingdom","United States","Uruguay","Uzbekistan","Vanuatu","Vatican City","Venezuela","Vietnam","Yemen","Zambia","Zimbabwe"],"label":"Country"} -->
<label for="input_e6c773f3"><span>Country</span></label><select name="input_e6c773f3" id="input_e6c773f3" defaultvalue="United States" class="wp-block-wpzoom-forms-select-field"><option value="Afghanistan">Afghanistan</option><option value="Albania">Albania</option><option value="Algeria">Algeria</option><option value="Andorra">Andorra</option><option value="Angola">Angola</option><option value="Antigua &amp; Deps">Antigua &amp; Deps</option><option value="Argentina">Argentina</option><option value="Armenia">Armenia</option><option value="Australia">Australia</option><option value="Austria">Austria</option><option value="Azerbaijan">Azerbaijan</option><option value="Bahamas">Bahamas</option><option value="Bahrain">Bahrain</option><option value="Bangladesh">Bangladesh</option><option value="Barbados">Barbados</option><option value="Belarus">Belarus</option><option value="Belgium">Belgium</option><option value="Belize">Belize</option><option value="Benin">Benin</option><option value="Bhutan">Bhutan</option><option value="Bolivia">Bolivia</option><option value="Bosnia Herzegovina">Bosnia Herzegovina</option><option value="Botswana">Botswana</option><option value="Brazil">Brazil</option><option value="Brunei">Brunei</option><option value="Bulgaria">Bulgaria</option><option value="Burkina">Burkina</option><option value="Burundi">Burundi</option><option value="Cambodia">Cambodia</option><option value="Cameroon">Cameroon</option><option value="Canada">Canada</option><option value="Cape Verde">Cape Verde</option><option value="Central African Rep">Central African Rep</option><option value="Chad">Chad</option><option value="Chile">Chile</option><option value="China">China</option><option value="Colombia">Colombia</option><option value="Comoros">Comoros</option><option value="Congo">Congo</option><option value="Congo (Democratic Rep)">Congo (Democratic Rep)</option><option value="Costa Rica">Costa Rica</option><option value="Croatia">Croatia</option><option value="Cuba">Cuba</option><option value="Cyprus">Cyprus</option><option value="Czech Republic">Czech Republic</option><option value="Denmark">Denmark</option><option value="Djibouti">Djibouti</option><option value="Dominica">Dominica</option><option value="Dominican Republic">Dominican Republic</option><option value="East Timor">East Timor</option><option value="Ecuador">Ecuador</option><option value="Egypt">Egypt</option><option value="El Salvador">El Salvador</option><option value="Equatorial Guinea">Equatorial Guinea</option><option value="Eritrea">Eritrea</option><option value="Estonia">Estonia</option><option value="Ethiopia">Ethiopia</option><option value="Fiji">Fiji</option><option value="Finland">Finland</option><option value="France">France</option><option value="Gabon">Gabon</option><option value="Gambia">Gambia</option><option value="Georgia">Georgia</option><option value="Germany">Germany</option><option value="Ghana">Ghana</option><option value="Greece">Greece</option><option value="Grenada">Grenada</option><option value="Guatemala">Guatemala</option><option value="Guinea">Guinea</option><option value="Guinea-Bissau">Guinea-Bissau</option><option value="Guyana">Guyana</option><option value="Haiti">Haiti</option><option value="Honduras">Honduras</option><option value="Hungary">Hungary</option><option value="Iceland">Iceland</option><option value="India">India</option><option value="Indonesia">Indonesia</option><option value="Iran">Iran</option><option value="Iraq">Iraq</option><option value="Ireland {Republic}">Ireland {Republic}</option><option value="Israel">Israel</option><option value="Italy">Italy</option><option value="Ivory Coast">Ivory Coast</option><option value="Jamaica">Jamaica</option><option value="Japan">Japan</option><option value="Jordan">Jordan</option><option value="Kazakhstan">Kazakhstan</option><option value="Kenya">Kenya</option><option value="Kiribati">Kiribati</option><option value="Korea North">Korea North</option><option value="Korea South">Korea South</option><option value="Kosovo">Kosovo</option><option value="Kuwait">Kuwait</option><option value="Kyrgyzstan">Kyrgyzstan</option><option value="Laos">Laos</option><option value="Latvia">Latvia</option><option value="Lebanon">Lebanon</option><option value="Lesotho">Lesotho</option><option value="Liberia">Liberia</option><option value="Libya">Libya</option><option value="Liechtenstein">Liechtenstein</option><option value="Lithuania">Lithuania</option><option value="Luxembourg">Luxembourg</option><option value="Macedonia">Macedonia</option><option value="Madagascar">Madagascar</option><option value="Malawi">Malawi</option><option value="Malaysia">Malaysia</option><option value="Maldives">Maldives</option><option value="Mali">Mali</option><option value="Malta">Malta</option><option value="Marshall Islands">Marshall Islands</option><option value="Mauritania">Mauritania</option><option value="Mauritius">Mauritius</option><option value="Mexico">Mexico</option><option value="Micronesia">Micronesia</option><option value="Moldova">Moldova</option><option value="Monaco">Monaco</option><option value="Mongolia">Mongolia</option><option value="Montenegro">Montenegro</option><option value="Morocco">Morocco</option><option value="Mozambique">Mozambique</option><option value="Myanmar, {Burma}">Myanmar, {Burma}</option><option value="Namibia">Namibia</option><option value="Nauru">Nauru</option><option value="Nepal">Nepal</option><option value="Netherlands">Netherlands</option><option value="New Zealand">New Zealand</option><option value="Nicaragua">Nicaragua</option><option value="Niger">Niger</option><option value="Nigeria">Nigeria</option><option value="Norway">Norway</option><option value="Oman">Oman</option><option value="Pakistan">Pakistan</option><option value="Palau">Palau</option><option value="Panama">Panama</option><option value="Papua New Guinea">Papua New Guinea</option><option value="Paraguay">Paraguay</option><option value="Peru">Peru</option><option value="Philippines">Philippines</option><option value="Poland">Poland</option><option value="Portugal">Portugal</option><option value="Qatar">Qatar</option><option value="Romania">Romania</option><option value="Russian Federation">Russian Federation</option><option value="Rwanda">Rwanda</option><option value="St Kitts &amp; Nevis">St Kitts &amp; Nevis</option><option value="St Lucia">St Lucia</option><option value="Saint Vincent &amp; the Grenadines">Saint Vincent &amp; the Grenadines</option><option value="Samoa">Samoa</option><option value="San Marino">San Marino</option><option value="Sao Tome &amp; Principe">Sao Tome &amp; Principe</option><option value="Saudi Arabia">Saudi Arabia</option><option value="Senegal">Senegal</option><option value="Serbia">Serbia</option><option value="Seychelles">Seychelles</option><option value="Sierra Leone">Sierra Leone</option><option value="Singapore">Singapore</option><option value="Slovakia">Slovakia</option><option value="Slovenia">Slovenia</option><option value="Solomon Islands">Solomon Islands</option><option value="Somalia">Somalia</option><option value="South Africa">South Africa</option><option value="South Sudan">South Sudan</option><option value="Spain">Spain</option><option value="Sri Lanka">Sri Lanka</option><option value="Sudan">Sudan</option><option value="Suriname">Suriname</option><option value="Swaziland">Swaziland</option><option value="Sweden">Sweden</option><option value="Switzerland">Switzerland</option><option value="Syria">Syria</option><option value="Taiwan">Taiwan</option><option value="Tajikistan">Tajikistan</option><option value="Tanzania">Tanzania</option><option value="Thailand">Thailand</option><option value="Togo">Togo</option><option value="Tonga">Tonga</option><option value="Trinidad &amp; Tobago">Trinidad &amp; Tobago</option><option value="Tunisia">Tunisia</option><option value="Turkey">Turkey</option><option value="Turkmenistan">Turkmenistan</option><option value="Tuvalu">Tuvalu</option><option value="Uganda">Uganda</option><option value="Ukraine">Ukraine</option><option value="United Arab Emirates">United Arab Emirates</option><option value="United Kingdom">United Kingdom</option><option value="United States">United States</option><option value="Uruguay">Uruguay</option><option value="Uzbekistan">Uzbekistan</option><option value="Vanuatu">Vanuatu</option><option value="Vatican City">Vatican City</option><option value="Venezuela">Venezuela</option><option value="Vietnam">Vietnam</option><option value="Yemen">Yemen</option><option value="Zambia">Zambia</option><option value="Zimbabwe">Zimbabwe</option></select>
<!-- /wp:wpzoom-forms/select-field --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:spacer {"height":"40px"} -->
<div style="height:40px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:wpzoom-forms/text-plain-field {"id":"input_subject","name":"Subject","label":"Subject","subject":true} -->
<label for="input_subject"><span>Subject</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_subject" id="input_subject" placeholder="" required data-subject="true" class="wp-block-wpzoom-forms-text-plain-field"/>
<!-- /wp:wpzoom-forms/text-plain-field -->

<!-- wp:wpzoom-forms/textarea-field {"id":"input_message","name":"Message"} -->
<label for="input_message"><span>Message</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><textarea name="input_message" id="input_message" cols="55" rows="10" placeholder="" required class="wp-block-wpzoom-forms-textarea-field"></textarea>
<!-- /wp:wpzoom-forms/textarea-field --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:columns {"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"30%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:30%"><!-- wp:wpzoom-forms/submit-field {"id":"input_submit"} -->
<input type="submit" id="input_submit" value="Submit" class="wp-block-wpzoom-forms-submit-field"/>
<!-- /wp:wpzoom-forms/submit-field --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"70%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:70%"><!-- wp:paragraph {"align":"right","style":{"typography":{"fontSize":16}}} -->
<p class="has-text-align-right" style="font-size:16px">Fields marked with <strong class="has-accent-color has-text-color">*</strong> are required.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group --></div>
<!-- /wp:wpzoom-forms/form -->'
	),
	array(
		'id'      => 'order-form',
		'name'    => 'Quote Request',
		
		'icon'    => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#1f1f1f"><path d="M456.92-255v22.69q0 6.92 5.2 12.12 5.19 5.19 12.11 5.19h24.62q7.23 0 12.65-5.42 5.42-5.43 5.42-12.66V-255h50q12.75 0 21.38-8.63 8.62-8.62 8.62-21.37v-120q0-12.75-8.62-21.37-8.63-8.63-21.38-8.63h-130v-60h130q12.75 0 21.38-8.63 8.62-8.63 8.62-21.38 0-12.76-8.62-21.37-8.63-8.62-21.38-8.62h-50v-22.69q0-6.93-5.19-12.12-5.19-5.19-12.11-5.19H475q-7.23 0-12.65 5.42-5.43 5.43-5.43 12.66V-555h-50q-12.75 0-21.37 8.63-8.63 8.62-8.63 21.37v120q0 12.75 8.63 21.37 8.62 8.63 21.37 8.63h130v60h-130q-12.75 0-21.37 8.63-8.63 8.63-8.63 21.38 0 12.76 8.63 21.37 8.62 8.62 21.37 8.62h50ZM252.31-100Q222-100 201-121q-21-21-21-51.31v-615.38Q180-818 201-839q21-21 51.31-21h342.3L780-674.61v502.3Q780-142 759-121q-21 21-51.31 21H252.31Zm0-60h455.38q4.62 0 8.46-3.85 3.85-3.84 3.85-8.46v-472.31H600.77q-15.37 0-25.76-10.39-10.39-10.39-10.39-25.76V-800H252.31q-4.62 0-8.46 3.85-3.85 3.84-3.85 8.46v615.38q0 4.62 3.85 8.46 3.84 3.85 8.46 3.85ZM240-800v155.38V-800-160v-640Z"/></svg>',

		'desc'    => 'Let visitors request a service quote by filling out this form.',
		'content' => '<!-- wp:wpzoom-forms/form -->
<div class="wp-block-wpzoom-forms-form"><!-- wp:group -->
<div class="wp-block-group"><!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"width":"100%"} -->
<div class="wp-block-column" style="flex-basis:100%"><!-- wp:wpzoom-forms/text-name-field {"id":"input_name","name":"Your Name","label":"Your Name"} -->
<label for="input_name"><span>Your Name</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_name" id="input_name" placeholder="" required class="wp-block-wpzoom-forms-text-name-field"/>
<!-- /wp:wpzoom-forms/text-name-field -->

<!-- wp:wpzoom-forms/text-email-field {"id":"input_email","name":"Your Email","label":"Your Email","replyto":true} -->
<label for="input_email"><span>Your Email</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="email" name="input_email" id="input_email" placeholder="" required data-replyto="true" class="wp-block-wpzoom-forms-text-email-field"/>
<!-- /wp:wpzoom-forms/text-email-field -->

<!-- wp:wpzoom-forms/text-phone-field {"id":"input_9ff254c3","name":"Phone Number","label":"Phone Number"} -->
<label for="input_9ff254c3"><span>Phone Number</span></label><input type="tel" name="input_9ff254c3" id="input_9ff254c3" placeholder="" class="wp-block-wpzoom-forms-text-phone-field"/>
<!-- /wp:wpzoom-forms/text-phone-field -->

<!-- wp:wpzoom-forms/text-plain-field {"id":"input_984c7374","name":"Your Address","label":"Your Address"} -->
<label for="input_984c7374"><span>Your Address</span></label><input type="text" name="input_984c7374" id="input_984c7374" placeholder="" data-subject="false" class="wp-block-wpzoom-forms-text-plain-field"/>
<!-- /wp:wpzoom-forms/text-plain-field -->

<!-- wp:wpzoom-forms/multi-checkbox-field {"id":"input_b627c5c7","name":"Required service(s)","options":["General maintenance","Repairs","House keeping","Landscape gardening","Other"],"label":"Required service(s)"} -->
<label for="input_b627c5c7"><span>Required service(s)</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><ul class="wp-block-wpzoom-forms-multi-checkbox-field"><li><label><input type="checkbox" name="input_b627c5c7[]" id="input_b627c5c7" value="General maintenance" required/>General maintenance</label></li><li><label><input type="checkbox" name="input_b627c5c7[]" id="input_b627c5c7" value="Repairs" required/>Repairs</label></li><li><label><input type="checkbox" name="input_b627c5c7[]" id="input_b627c5c7" value="House keeping" required/>House keeping</label></li><li><label><input type="checkbox" name="input_b627c5c7[]" id="input_b627c5c7" value="Landscape gardening" required/>Landscape gardening</label></li><li><label><input type="checkbox" name="input_b627c5c7[]" id="input_b627c5c7" value="Other" required/>Other</label></li></ul>
<!-- /wp:wpzoom-forms/multi-checkbox-field -->

<!-- wp:spacer {"height":"34px"} -->
<div style="height:34px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:wpzoom-forms/textarea-field {"id":"input_message","name":"How can we help you?","label":"How can we help you?"} -->
<label for="input_message"><span>How can we help you?</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><textarea name="input_message" id="input_message" cols="55" rows="10" placeholder="" required class="wp-block-wpzoom-forms-textarea-field"></textarea>
<!-- /wp:wpzoom-forms/textarea-field --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:columns {"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center"><!-- wp:column {"verticalAlignment":"center","width":"30%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:30%"><!-- wp:wpzoom-forms/submit-field {"id":"input_submit"} -->
<input type="submit" id="input_submit" value="Submit" class="wp-block-wpzoom-forms-submit-field"/>
<!-- /wp:wpzoom-forms/submit-field --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"70%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:70%"><!-- wp:paragraph {"align":"right","style":{"typography":{"fontSize":16}}} -->
<p class="has-text-align-right" style="font-size:16px">Fields marked with <strong class="has-accent-color has-text-color">*</strong> are required.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group --></div>
<!-- /wp:wpzoom-forms/form -->'
	),
	array(
		'id'      => 'feedback-form',
		'name'    => 'Feedback Form',
		
		'icon'    => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#1f1f1f"><path d="M480-371.54q13.92 0 23.11-9.19 9.2-9.19 9.2-23.12 0-13.92-9.2-23.11-9.19-9.19-23.11-9.19t-23.11 9.19q-9.2 9.19-9.2 23.11 0 13.93 9.2 23.12 9.19 9.19 23.11 9.19Zm21.38-147.84Q510-528 510-540.77v-181.54q0-12.77-8.62-21.38-8.61-8.62-21.38-8.62t-21.38 8.62q-8.62 8.61-8.62 21.38v181.54q0 12.77 8.62 21.39 8.61 8.61 21.38 8.61t21.38-8.61ZM241.54-260l-80.08 80.07q-17.07 17.08-39.27 7.74Q100-181.54 100-205.85v-581.84Q100-818 121-839q21-21 51.31-21h615.38Q818-860 839-839q21 21 21 51.31v455.38Q860-302 839-281q-21 21-51.31 21H241.54ZM216-320h571.69q4.62 0 8.46-3.85 3.85-3.84 3.85-8.46v-455.38q0-4.62-3.85-8.46-3.84-3.85-8.46-3.85H172.31q-4.62 0-8.46 3.85-3.85 3.84-3.85 8.46v523.08L216-320Zm-56 0v-480 480Z"/></svg>',

		'desc'    => 'Gather feedback or testimonials from users or customers.',
		'content' => '<!-- wp:wpzoom-forms/form -->
<div class="wp-block-wpzoom-forms-form"><!-- wp:group -->
<div class="wp-block-group"><!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"width":"100%"} -->
<div class="wp-block-column" style="flex-basis:100%"><!-- wp:wpzoom-forms/text-name-field {"id":"input_name","name":"Name"} -->
<label for="input_name"><span>Name</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_name" id="input_name" placeholder="" required class="wp-block-wpzoom-forms-text-name-field"/>
<!-- /wp:wpzoom-forms/text-name-field -->

<!-- wp:wpzoom-forms/text-email-field {"id":"input_email","name":"Email","replyto":true} -->
<label for="input_email"><span>Email</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="email" name="input_email" id="input_email" placeholder="" required data-replyto="true" class="wp-block-wpzoom-forms-text-email-field"/>
<!-- /wp:wpzoom-forms/text-email-field -->

<!-- wp:wpzoom-forms/select-field {"id":"input_8e4aa5c6","name":"Department","options":["Sales","Customer Support","Marketing","Development","Other"],"label":"Which department do you have a suggestion for?"} -->
<label for="input_8e4aa5c6"><span>Which department do you have a suggestion for?</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><select name="input_8e4aa5c6" id="input_8e4aa5c6" required defaultvalue="Sales" class="wp-block-wpzoom-forms-select-field"><option value="Sales">Sales</option><option value="Customer Support">Customer Support</option><option value="Marketing">Marketing</option><option value="Development">Development</option><option value="Other">Other</option></select>
<!-- /wp:wpzoom-forms/select-field -->

<!-- wp:wpzoom-forms/text-plain-field {"id":"input_subject","name":"Subject","label":"Subject","subject":true} -->
<label for="input_subject"><span>Subject</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_subject" id="input_subject" placeholder="" required data-subject="true" class="wp-block-wpzoom-forms-text-plain-field"/>
<!-- /wp:wpzoom-forms/text-plain-field -->

<!-- wp:wpzoom-forms/textarea-field {"id":"input_message","name":"Message"} -->
<label for="input_message"><span>Message</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><textarea name="input_message" id="input_message" cols="55" rows="10" placeholder="" required class="wp-block-wpzoom-forms-textarea-field"></textarea>
<!-- /wp:wpzoom-forms/textarea-field --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"width":"30%"} -->
<div class="wp-block-column" style="flex-basis:30%"><!-- wp:wpzoom-forms/submit-field {"id":"input_submit"} -->
<input type="submit" id="input_submit" value="Submit" class="wp-block-wpzoom-forms-submit-field"/>
<!-- /wp:wpzoom-forms/submit-field --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"70%"} -->
<div class="wp-block-column" style="flex-basis:70%"><!-- wp:paragraph {"align":"right","style":{"typography":{"fontSize":16}}} -->
<p class="has-text-align-right" style="font-size:16px">Fields marked with <strong class="has-accent-color has-text-color">*</strong> are required.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group --></div>
<!-- /wp:wpzoom-forms/form -->'
	),
	array(
		'id'       => 'restaurant-callback-request',
		'name'     => 'Restaurant Callback',
		'category' => 'restaurant',
		'icon'     => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#1f1f1f"><path d="M290-595.38V-840q0-12.75 8.63-21.37 8.63-8.63 21.38-8.63 12.76 0 21.37 8.63Q350-852.75 350-840v244.62h55.39V-840q0-12.75 8.62-21.37 8.63-8.63 21.39-8.63 12.75 0 21.37 8.63 8.61 8.62 8.61 21.37v244.62q0 53.69-33.34 92.42-33.35 38.73-82.04 49.27V-120q0 12.75-8.63 21.37Q332.74-90 319.99-90q-12.76 0-21.37-8.63Q290-107.25 290-120v-333.69q-48.69-10.54-82.04-49.27-33.34-38.73-33.34-92.42V-840q0-12.75 8.63-21.37 8.62-8.63 21.38-8.63 12.75 0 21.37 8.63 8.61 8.62 8.61 21.37v244.62H290ZM674.61-410h-73.02q-15.51 0-25.86-10.39-10.34-10.4-10.34-25.76V-680q0-75.39 43.61-132.69Q652.61-870 697.61-870q16.85 0 26.93 12.08 10.07 12.07 10.07 30.31V-120q0 12.75-8.63 21.37Q717.36-90 704.6-90q-12.75 0-21.37-8.63-8.62-8.62-8.62-21.37v-290Z"/></svg>',
		'desc'     => 'Take table callback requests with party size, date, and special requests.',
		'content'  => '<!-- wp:wpzoom-forms/form -->
<div class="wp-block-wpzoom-forms-form"><!-- wp:wpzoom-forms/text-name-field {"label":"Name","required":true,"id":"input_f18b1404","name":"Name"} -->
<label for="input_f18b1404"><span>Name</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_f18b1404" id="input_f18b1404" placeholder="" required class="wp-block-wpzoom-forms-text-name-field"/>
<!-- /wp:wpzoom-forms/text-name-field -->

<!-- wp:wpzoom-forms/text-email-field {"id":"input_09327d6e","name":"Email","replyto":true} -->
<label for="input_09327d6e"><span>Email</span></label><input type="email" name="input_09327d6e" id="input_09327d6e" placeholder="" data-replyto="true" class="wp-block-wpzoom-forms-text-email-field"/>
<!-- /wp:wpzoom-forms/text-email-field -->

<!-- wp:wpzoom-forms/text-phone-field {"label":"Phone","required":true,"id":"input_95c1b0df","name":"Phone"} -->
<label for="input_95c1b0df"><span>Phone</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="tel" name="input_95c1b0df" id="input_95c1b0df" placeholder="" required class="wp-block-wpzoom-forms-text-phone-field"/>
<!-- /wp:wpzoom-forms/text-phone-field -->

<!-- wp:wpzoom-forms/datepicker-field {"label":"Preferred Date","required":true,"id":"input_d05391b2","name":"Preferred Date"} -->
<label for="input_d05391b2"><span>Preferred Date</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input data-datepicker="true" autocomplete="off" data-date-format="Y-m-d" data-mode="single" type="text" name="input_d05391b2" id="input_d05391b2" placeholder="Y-m-d" required class="wp-block-wpzoom-forms-datepicker-field"/>
<!-- /wp:wpzoom-forms/datepicker-field -->

<!-- wp:wpzoom-forms/select-field {"label":"Party Size","options":["1","2","3","4","5","6","7","8","9","10+"],"required":true,"id":"input_71f1887d","name":"Party Size","defaultValue":"1"} -->
<label for="input_71f1887d"><span>Party Size</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><select name="input_71f1887d" id="input_71f1887d" required defaultvalue="1" class="wp-block-wpzoom-forms-select-field"><option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option><option value="5">5</option><option value="6">6</option><option value="7">7</option><option value="8">8</option><option value="9">9</option><option value="10+">10+</option></select>
<!-- /wp:wpzoom-forms/select-field -->

<!-- wp:wpzoom-forms/textarea-field {"label":"Special Requests","id":"input_e245b5a0","name":"Special Requests"} -->
<label for="input_e245b5a0"><span>Special Requests</span></label><textarea name="input_e245b5a0" id="input_e245b5a0" cols="55" rows="10" placeholder="" class="wp-block-wpzoom-forms-textarea-field"></textarea>
<!-- /wp:wpzoom-forms/textarea-field -->

<!-- wp:wpzoom-forms/submit-field {"label":"Request Callback","id":"input_submit","name":"Request Callback"} -->
<input type="submit" id="input_submit" value="Request Callback" class="wp-block-wpzoom-forms-submit-field"/>
<!-- /wp:wpzoom-forms/submit-field --></div>
<!-- /wp:wpzoom-forms/form -->',
	),
	array(
		'id'       => 'partnership-contact-lite',
		'name'     => 'Partnership Contact',
		'category' => 'business',
		'icon'     => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#1f1f1f"><path d="M471.54-161.15q5.92 0 12.04-2.77 6.11-2.77 9.65-6.31l319.92-319.92q13.54-13.54 20.39-28.93 6.84-15.38 6.84-32.3 0-17.54-6.84-33.77-6.85-16.24-20.39-29.16l-160-160q-12.92-13.54-28-19.81-15.07-6.26-32.61-6.26-16.92 0-32.5 6.26-15.58 6.27-28.73 19.81l-22.93 22.93 74 74.61q13.46 12.85 19.89 29.31 6.42 16.46 6.42 34.15 0 36.62-24.46 61.08t-61.08 24.46q-17.69 0-34.27-5.85-16.57-5.84-29.42-18.69l-66.92-66.3q-3.46-3.47-8.85-3.47-5.38 0-8.84 3.47l-165 164.99q-4.54 4.54-6.81 10.16-2.27 5.61-2.27 11.54 0 11.07 7.54 18.92 7.54 7.85 18.61 7.85 5.93 0 12.04-2.77 6.12-2.77 9.66-6.31l110.3-110.31q8.31-8.31 20.58-8.81 12.27-.5 21.58 8.81 8.69 8.69 8.69 21.08 0 12.38-8.69 21.07l-109.7 110.31q-4.53 4.54-6.8 10.16-2.27 5.61-2.27 11.54 0 10.69 7.73 18.42 7.73 7.73 18.42 7.73 5.93 0 12.04-2.77 6.12-2.77 9.65-6.31l114.93-114.31q8.31-8.3 20.57-8.8 12.27-.5 21.58 8.8 8.69 8.7 8.69 21.08 0 12.38-8.69 21.08L372.92-290.54q-4.15 3.54-6.61 9.65-2.46 6.12-2.46 12.04 0 10.7 7.73 18.43t18.42 7.73q5.92 0 11.54-2.27 5.61-2.27 10.15-6.81l114.93-114.31q8.3-8.31 20.57-8.81t21.58 8.81q8.69 8.69 8.69 21.08 0 12.38-8.69 21.08L453.85-209q-4.54 4.54-6.81 10.54-2.27 6-2.27 11.54 0 11.07 8.23 18.42 8.23 7.35 18.54 7.35Zm-.62 59.99q-33.92 0-59.15-23.53-25.23-23.54-26.38-58.62-34-2.31-56.81-24.15-22.81-21.85-24.73-57.39-35.54-2.3-57.46-24.84-21.93-22.54-23.47-56.7-35.69-2.3-58.92-25.88-23.23-23.58-23.23-59.65 0-17.69 6.73-34.66 6.73-16.96 19.58-29.8l165.38-165.39q20.69-20.69 50.92-20.69 30.24 0 50.93 20.69l66.54 66.54q3.53 4.15 9.26 6.62 5.74 2.46 12.43 2.46 10.92 0 18.84-7.23 7.93-7.23 7.93-18.93 0-6.69-2.46-12.42-2.47-5.73-6.62-9.27L399.92-774.31q-12.92-13.54-28.19-19.81-15.27-6.26-32.81-6.26-16.92 0-32.11 6.26-15.2 6.27-28.73 19.81l-131.39 132q-17.46 17.46-23.46 42.04t1.23 47.81q2.62 12.38-4.5 22.5-7.11 10.11-19.5 12.11-12.38 2-22.69-4.8-10.31-6.81-12.31-19.2-9.46-38.38-.07-76.42 9.38-38.04 38.53-67.19l131-131q22.47-21.85 48.89-32.88 26.42-11.04 55.5-11.04 29.07 0 55.3 11.04 26.24 11.03 48.08 32.88l22.93 22.92 22.92-22.92q22.46-21.85 48.69-32.88 26.23-11.04 55.31-11.04 29.08 0 55.5 11.04 26.42 11.03 48.27 32.88l159 159q21.84 21.85 33.46 49.73 11.61 27.88 11.61 56.96 0 29.08-11.61 55.31-11.62 26.23-33.46 48.07L535.38-128.08q-13.23 13.23-29.8 20.08-16.58 6.84-34.66 6.84Zm-112.61-532.3Z"/></svg>',
		'desc'     => 'Capture company, partnership type, and idea details from potential partners.',
		'content'  => '<!-- wp:wpzoom-forms/form -->
<div class="wp-block-wpzoom-forms-form"><!-- wp:wpzoom-forms/text-name-field {"label":"Name","required":true,"id":"input_0f9e9122","name":"Name"} -->
<label for="input_0f9e9122"><span>Name</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_0f9e9122" id="input_0f9e9122" placeholder="" required class="wp-block-wpzoom-forms-text-name-field"/>
<!-- /wp:wpzoom-forms/text-name-field -->

<!-- wp:wpzoom-forms/text-email-field {"label":"Email","required":true,"id":"input_f3b6b8d0","name":"Email","replyto":true} -->
<label for="input_f3b6b8d0"><span>Email</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="email" name="input_f3b6b8d0" id="input_f3b6b8d0" placeholder="" required data-replyto="true" class="wp-block-wpzoom-forms-text-email-field"/>
<!-- /wp:wpzoom-forms/text-email-field -->

<!-- wp:wpzoom-forms/text-plain-field {"label":"Company","placeholder":"Your Company Name","id":"input_eb6ac447","name":"Company"} -->
<label for="input_eb6ac447"><span>Company</span></label><input type="text" name="input_eb6ac447" id="input_eb6ac447" placeholder="Your Company Name" data-subject="false" class="wp-block-wpzoom-forms-text-plain-field"/>
<!-- /wp:wpzoom-forms/text-plain-field -->

<!-- wp:wpzoom-forms/select-field {"label":"Partnership Type","options":["strategic","affiliate","joint_venture","other"],"required":true,"id":"input_748c46bc","name":"Partnership Type","defaultValue":"strategic"} -->
<label for="input_748c46bc"><span>Partnership Type</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><select name="input_748c46bc" id="input_748c46bc" required defaultvalue="strategic" class="wp-block-wpzoom-forms-select-field"><option value="strategic">strategic</option><option value="affiliate">affiliate</option><option value="joint_venture">joint_venture</option><option value="other">other</option></select>
<!-- /wp:wpzoom-forms/select-field -->

<!-- wp:wpzoom-forms/textarea-field {"label":"Idea Details","placeholder":"Describe your partnership idea in detail","required":true,"id":"input_eeec6268","name":"Idea Details"} -->
<label for="input_eeec6268"><span>Idea Details</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><textarea name="input_eeec6268" id="input_eeec6268" cols="55" rows="10" placeholder="Describe your partnership idea in detail" required class="wp-block-wpzoom-forms-textarea-field"></textarea>
<!-- /wp:wpzoom-forms/textarea-field -->

<!-- wp:wpzoom-forms/submit-field {"label":"Submit Inquiry","id":"input_submit","name":"Submit Inquiry"} -->
<input type="submit" id="input_submit" value="Submit Inquiry" class="wp-block-wpzoom-forms-submit-field"/>
<!-- /wp:wpzoom-forms/submit-field --></div>
<!-- /wp:wpzoom-forms/form -->',
	),
	array(
		'id'       => 'product-feedback-lite',
		'name'     => 'Product Feedback',
		'category' => 'surveys',
		'icon'     => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#1f1f1f"><path d="m384-334 96-74 96 74-36-122 90-64H518l-38-124-38 124H330l90 64-36 122Zm96 1.38L345.46-230q-8.69 6.69-18.42 6.19-9.73-.5-17.42-5.58-7.69-5.07-11.85-14.19-4.15-9.11-.31-19.65l51.62-168.46-130-93q-9.31-6.08-11.73-15.81t.81-18.42q3.23-8.69 10.73-14.89 7.5-6.19 18.04-6.19h161.69l52.61-173.54q3.85-10.53 11.66-16.3 7.8-5.77 17.11-5.77 9.31 0 17.11 5.77 7.81 5.77 11.66 16.3L561.38-580h161.69q10.54 0 18.04 6.19 7.5 6.2 10.73 14.89 3.23 8.69.81 18.42-2.42 9.73-11.73 15.81l-130 93 51.62 168.46q3.84 10.54-.31 19.65-4.16 9.12-11.85 14.19-7.69 5.08-17.42 5.58T614.54-230L480-332.62ZM480-489Z"/></svg>',
		'desc'     => 'Gather overall rating, improvement areas, and open comments on your product.',
		'content'  => '<!-- wp:wpzoom-forms/form -->
<div class="wp-block-wpzoom-forms-form"><!-- wp:wpzoom-forms/text-name-field {"label":"Your Name","required":true,"id":"input_85833722","name":"Your Name"} -->
<label for="input_85833722"><span>Your Name</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_85833722" id="input_85833722" placeholder="" required class="wp-block-wpzoom-forms-text-name-field"/>
<!-- /wp:wpzoom-forms/text-name-field -->

<!-- wp:wpzoom-forms/text-email-field {"label":"Your Email","required":true,"id":"input_f1cd30f2","name":"Your Email","replyto":true} -->
<label for="input_f1cd30f2"><span>Your Email</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="email" name="input_f1cd30f2" id="input_f1cd30f2" placeholder="" required data-replyto="true" class="wp-block-wpzoom-forms-text-email-field"/>
<!-- /wp:wpzoom-forms/text-email-field -->

<!-- wp:wpzoom-forms/radio-field {"label":"Overall Rating","options":["Excellent","Good","Average","Poor"],"required":true,"id":"input_76ea7fe7","name":"Overall Rating","defaultValue":"Excellent"} -->
<label for="input_76ea7fe7"><span>Overall Rating</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><ul class="wp-block-wpzoom-forms-radio-field" id="input_76ea7fe7"><li><label><input type="radio" name="Overall Rating" id="input_76ea7fe7-0" value="Excellent" checked required/>Excellent</label></li><li><label><input type="radio" name="Overall Rating" id="input_76ea7fe7-1" value="Good" required/>Good</label></li><li><label><input type="radio" name="Overall Rating" id="input_76ea7fe7-2" value="Average" required/>Average</label></li><li><label><input type="radio" name="Overall Rating" id="input_76ea7fe7-3" value="Poor" required/>Poor</label></li></ul>
<!-- /wp:wpzoom-forms/radio-field -->

<!-- wp:wpzoom-forms/multi-checkbox-field {"label":"Areas for Improvement","options":["Usability","Features","Performance","Customer Support"],"id":"input_17757a75","name":"Areas for Improvement"} -->
<label for="input_17757a75"><span>Areas for Improvement</span></label><ul class="wp-block-wpzoom-forms-multi-checkbox-field" id="input_17757a75"><li><label><input type="checkbox" name="input_17757a75[]" id="input_17757a75-0" value="Usability"/>Usability</label></li><li><label><input type="checkbox" name="input_17757a75[]" id="input_17757a75-1" value="Features"/>Features</label></li><li><label><input type="checkbox" name="input_17757a75[]" id="input_17757a75-2" value="Performance"/>Performance</label></li><li><label><input type="checkbox" name="input_17757a75[]" id="input_17757a75-3" value="Customer Support"/>Customer Support</label></li></ul>
<!-- /wp:wpzoom-forms/multi-checkbox-field -->

<!-- wp:wpzoom-forms/textarea-field {"label":"Comments","placeholder":"Please provide any additional comments or suggestions.","id":"input_2a7e455d","name":"Comments"} -->
<label for="input_2a7e455d"><span>Comments</span></label><textarea name="input_2a7e455d" id="input_2a7e455d" cols="55" rows="10" placeholder="Please provide any additional comments or suggestions." class="wp-block-wpzoom-forms-textarea-field"></textarea>
<!-- /wp:wpzoom-forms/textarea-field -->

<!-- wp:wpzoom-forms/submit-field {"label":"Submit Feedback","id":"input_submit","name":"Submit Feedback"} -->
<input type="submit" id="input_submit" value="Submit Feedback" class="wp-block-wpzoom-forms-submit-field"/>
<!-- /wp:wpzoom-forms/submit-field --></div>
<!-- /wp:wpzoom-forms/form -->',
	),
	array(
		'id'       => 'lead-capture',
		'name'     => 'Lead Capture',
		'category' => 'business',
		'icon'     => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#1f1f1f"><path d="M725-530h-90q-12.75 0-21.37-8.63-8.63-8.63-8.63-21.38 0-12.76 8.63-21.37Q622.25-590 635-590h90v-90q0-12.75 8.63-21.37 8.63-8.63 21.38-8.63 12.76 0 21.37 8.63Q785-692.75 785-680v90h90q12.75 0 21.37 8.63 8.63 8.63 8.63 21.38 0 12.76-8.63 21.37Q887.75-530 875-530h-90v90q0 12.75-8.63 21.37-8.63 8.63-21.38 8.63-12.76 0-21.37-8.63Q725-427.25 725-440v-90Zm-463.96-3.35Q220-574.38 220-632.31q0-57.92 41.04-98.96 41.04-41.04 98.96-41.04 57.92 0 98.96 41.04Q500-690.23 500-632.31q0 57.93-41.04 98.96-41.04 41.04-98.96 41.04-57.92 0-98.96-41.04ZM60-248.46v-28.16q0-29.38 15.96-54.42 15.96-25.04 42.66-38.5 59.3-29.07 119.65-43.61 60.35-14.54 121.73-14.54t121.73 14.54q60.35 14.54 119.65 43.61 26.7 13.46 42.66 38.5Q660-306 660-276.62v28.16q0 25.3-17.73 43.04-17.73 17.73-43.04 17.73H120.77q-25.31 0-43.04-17.73Q60-223.16 60-248.46Zm60 .77h480v-28.93q0-12.15-7.04-22.5-7.04-10.34-19.11-16.88-51.7-25.46-105.42-38.58Q414.7-367.69 360-367.69q-54.7 0-108.43 13.11-53.72 13.12-105.42 38.58-12.07 6.54-19.11 16.88-7.04 10.35-7.04 22.5v28.93Zm296.5-328.12q23.5-23.5 23.5-56.5t-23.5-56.5q-23.5-23.5-56.5-23.5t-56.5 23.5q-23.5 23.5-23.5 56.5t23.5 56.5q23.5 23.5 56.5 23.5t56.5-23.5Zm-56.5-56.5Zm0 384.62Z"/></svg>',
		'desc'     => 'Collect contact info, service type, and project details from new leads.',
		'content'  => '<!-- wp:wpzoom-forms/form -->
<div class="wp-block-wpzoom-forms-form"><!-- wp:wpzoom-forms/text-name-field {"label":"Name","required":true,"id":"input_3aa738d3","name":"Name"} -->
<label for="input_3aa738d3"><span>Name</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_3aa738d3" id="input_3aa738d3" placeholder="" required class="wp-block-wpzoom-forms-text-name-field"/>
<!-- /wp:wpzoom-forms/text-name-field -->

<!-- wp:wpzoom-forms/text-email-field {"label":"Email","required":true,"id":"input_93ea3142","name":"Email","replyto":true} -->
<label for="input_93ea3142"><span>Email</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="email" name="input_93ea3142" id="input_93ea3142" placeholder="" required data-replyto="true" class="wp-block-wpzoom-forms-text-email-field"/>
<!-- /wp:wpzoom-forms/text-email-field -->

<!-- wp:wpzoom-forms/text-phone-field {"label":"Phone","id":"input_c6eed50c","name":"Phone"} -->
<label for="input_c6eed50c"><span>Phone</span></label><input type="tel" name="input_c6eed50c" id="input_c6eed50c" placeholder="" class="wp-block-wpzoom-forms-text-phone-field"/>
<!-- /wp:wpzoom-forms/text-phone-field -->

<!-- wp:wpzoom-forms/select-field {"label":"Service Type","options":["web-design","web-development","seo","other"],"required":true,"id":"input_664d54b6","name":"Service Type","defaultValue":"web-design"} -->
<label for="input_664d54b6"><span>Service Type</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><select name="input_664d54b6" id="input_664d54b6" required defaultvalue="web-design" class="wp-block-wpzoom-forms-select-field"><option value="web-design">web-design</option><option value="web-development">web-development</option><option value="seo">seo</option><option value="other">other</option></select>
<!-- /wp:wpzoom-forms/select-field -->

<!-- wp:wpzoom-forms/textarea-field {"label":"Project Details","required":true,"id":"input_ab0d0a21","name":"Project Details"} -->
<label for="input_ab0d0a21"><span>Project Details</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><textarea name="input_ab0d0a21" id="input_ab0d0a21" cols="55" rows="10" placeholder="" required class="wp-block-wpzoom-forms-textarea-field"></textarea>
<!-- /wp:wpzoom-forms/textarea-field -->

<!-- wp:wpzoom-forms/submit-field {"label":"Submit","id":"input_submit","name":"Submit"} -->
<input type="submit" id="input_submit" value="Submit" class="wp-block-wpzoom-forms-submit-field"/>
<!-- /wp:wpzoom-forms/submit-field --></div>
<!-- /wp:wpzoom-forms/form -->',
	),
	array(
		'id'       => 'waitlist-optin',
		'name'     => 'Waitlist Signup',
		'category' => 'newsletter',
		'icon'     => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#1f1f1f"><path d="m480-261.54-158.77 68.15q-36.15 15.46-68.69-5.92Q220-220.69 220-259.46v-488.23Q220-778 241-799q21-21 51.31-21H490q12.77 0 21.38 8.62Q520-802.77 520-790t-8.62 21.38Q502.77-760 490-760H292.31q-4.62 0-8.46 3.85-3.85 3.84-3.85 8.46v486.84q0 6.54 5.58 10.39 5.57 3.84 11.73 1.15L480-328l182.69 78.69q6.16 2.69 11.73-1.15 5.58-3.85 5.58-10.39V-490q0-12.77 8.62-21.38Q697.23-520 710-520t21.38 8.62Q740-502.77 740-490v230.54q0 38.77-32.54 60.15-32.54 21.38-68.69 5.92L480-261.54ZM480-760H280h240-40Zm200 80h-50q-12.77 0-21.38-8.62Q600-697.23 600-710t8.62-21.38Q617.23-740 630-740h50v-50q0-12.77 8.62-21.38Q697.23-820 710-820t21.38 8.62Q740-802.77 740-790v50h50q12.77 0 21.38 8.62Q820-722.77 820-710t-8.62 21.38Q802.77-680 790-680h-50v50q0 12.77-8.62 21.38Q722.77-600 710-600t-21.38-8.62Q680-617.23 680-630v-50Z"/></svg>',
		'desc'     => 'Sign up early interest with optional notes and consent for launch updates.',
		'content'  => '<!-- wp:wpzoom-forms/form -->
<div class="wp-block-wpzoom-forms-form"><!-- wp:wpzoom-forms/text-name-field {"label":"Name","required":true,"id":"input_1c0ba376","name":"Name"} -->
<label for="input_1c0ba376"><span>Name</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_1c0ba376" id="input_1c0ba376" placeholder="" required class="wp-block-wpzoom-forms-text-name-field"/>
<!-- /wp:wpzoom-forms/text-name-field -->

<!-- wp:wpzoom-forms/text-email-field {"label":"Email","required":true,"id":"input_31012686","name":"Email","replyto":true} -->
<label for="input_31012686"><span>Email</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="email" name="input_31012686" id="input_31012686" placeholder="" required data-replyto="true" class="wp-block-wpzoom-forms-text-email-field"/>
<!-- /wp:wpzoom-forms/text-email-field -->

<!-- wp:wpzoom-forms/select-field {"label":"Interested In","options":["product-a","product-b","both"],"required":true,"id":"input_7fa818cd","name":"Interested In","defaultValue":"product-a"} -->
<label for="input_7fa818cd"><span>Interested In</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><select name="input_7fa818cd" id="input_7fa818cd" required defaultvalue="product-a" class="wp-block-wpzoom-forms-select-field"><option value="product-a">product-a</option><option value="product-b">product-b</option><option value="both">both</option></select>
<!-- /wp:wpzoom-forms/select-field -->

<!-- wp:wpzoom-forms/textarea-field {"label":"Tell us more about what you\'re looking for (optional)","id":"input_31602111","name":"Tell us more about what you\'re looking for (optional)"} -->
<label for="input_31602111"><span>Tell us more about what you\'re looking for (optional)</span></label><textarea name="input_31602111" id="input_31602111" cols="55" rows="10" placeholder="" class="wp-block-wpzoom-forms-textarea-field"></textarea>
<!-- /wp:wpzoom-forms/textarea-field -->

<!-- wp:wpzoom-forms/checkbox-field {"label":"I consent to receiving email updates about the product launch.","required":true,"id":"input_6253c694","name":"I consent to receiving email updates about the product launch."} -->
<div class="wp-block-wpzoom-forms-checkbox-field"><input type="checkbox" name="input_6253c694" id="input_6253c694" required/><label for="input_6253c694"><span>I consent to receiving email updates about the product launch.</span><sup class="wp-block-wpzoom-forms-required">*</sup></label></div>
<!-- /wp:wpzoom-forms/checkbox-field -->

<!-- wp:wpzoom-forms/submit-field {"label":"Join the Waitlist","id":"input_submit","name":"Join the Waitlist"} -->
<input type="submit" id="input_submit" value="Join the Waitlist" class="wp-block-wpzoom-forms-submit-field"/>
<!-- /wp:wpzoom-forms/submit-field --></div>
<!-- /wp:wpzoom-forms/form -->',
	),
	array(
		'id'       => 'demo-booking-basic',
		'name'     => 'Demo Request',
		'category' => 'business',
		'icon'     => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#1f1f1f"><path d="m417.92-336.54 199.92-128q8.24-5.61 8.24-15.46 0-9.85-8.24-15.46l-199.92-128q-9.23-6.23-18.57-1-9.35 5.23-9.35 16.08v256.76q0 10.85 9.35 16.08 9.34 5.23 18.57-1ZM172.31-180Q142-180 121-201q-21-21-21-51.31v-455.38Q100-738 121-759q21-21 51.31-21h615.38Q818-780 839-759q21 21 21 51.31v455.38Q860-222 839-201q-21 21-51.31 21H172.31Zm0-60h615.38q4.62 0 8.46-3.85 3.85-3.84 3.85-8.46v-455.38q0-4.62-3.85-8.46-3.84-3.85-8.46-3.85H172.31q-4.62 0-8.46 3.85-3.85 3.84-3.85 8.46v455.38q0 4.62 3.85 8.46 3.84 3.85 8.46 3.85ZM160-240v-480 480Z"/></svg>',
		'desc'     => 'Book product demos with company, preferred date, and optional discussion topics.',
		'content'  => '<!-- wp:wpzoom-forms/form -->
<div class="wp-block-wpzoom-forms-form"><!-- wp:wpzoom-forms/text-name-field {"label":"Name","required":true,"id":"input_dbc395ef","name":"Name"} -->
<label for="input_dbc395ef"><span>Name</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_dbc395ef" id="input_dbc395ef" placeholder="" required class="wp-block-wpzoom-forms-text-name-field"/>
<!-- /wp:wpzoom-forms/text-name-field -->

<!-- wp:wpzoom-forms/text-email-field {"label":"Business Email","required":true,"id":"input_02c3c928","name":"Business Email","replyto":true} -->
<label for="input_02c3c928"><span>Business Email</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="email" name="input_02c3c928" id="input_02c3c928" placeholder="" required data-replyto="true" class="wp-block-wpzoom-forms-text-email-field"/>
<!-- /wp:wpzoom-forms/text-email-field -->

<!-- wp:wpzoom-forms/text-plain-field {"label":"Company","placeholder":"Your Company Name","id":"input_4f1f9837","name":"Company"} -->
<label for="input_4f1f9837"><span>Company</span></label><input type="text" name="input_4f1f9837" id="input_4f1f9837" placeholder="Your Company Name" data-subject="false" class="wp-block-wpzoom-forms-text-plain-field"/>
<!-- /wp:wpzoom-forms/text-plain-field -->

<!-- wp:wpzoom-forms/datepicker-field {"label":"Preferred Demo Date","required":true,"id":"input_df963471","name":"Preferred Demo Date"} -->
<label for="input_df963471"><span>Preferred Demo Date</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input data-datepicker="true" autocomplete="off" data-date-format="Y-m-d" data-mode="single" type="text" name="input_df963471" id="input_df963471" placeholder="Y-m-d" required class="wp-block-wpzoom-forms-datepicker-field"/>
<!-- /wp:wpzoom-forms/datepicker-field -->

<!-- wp:wpzoom-forms/textarea-field {"label":"Notes","placeholder":"Any specific topics you\'d like us to cover?","id":"input_cf9597fa","name":"Notes"} -->
<label for="input_cf9597fa"><span>Notes</span></label><textarea name="input_cf9597fa" id="input_cf9597fa" cols="55" rows="10" placeholder="Any specific topics you&#039;d like us to cover?" class="wp-block-wpzoom-forms-textarea-field"></textarea>
<!-- /wp:wpzoom-forms/textarea-field -->

<!-- wp:wpzoom-forms/submit-field {"label":"Request Demo","id":"input_submit","name":"Request Demo"} -->
<input type="submit" id="input_submit" value="Request Demo" class="wp-block-wpzoom-forms-submit-field"/>
<!-- /wp:wpzoom-forms/submit-field --></div>
<!-- /wp:wpzoom-forms/form -->',
	),
	array(
		'id'       => 'website-audit-request',
		'name'     => 'Website Audit Request',
		'category' => 'business',
		'icon'     => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#1f1f1f"><path d="M100-480q0-78.77 29.96-148.11 29.96-69.35 81.27-120.66 51.31-51.31 120.66-81.27Q401.23-860 480-860q129.31 0 227.85 75.96 98.53 75.97 133.15 193.5 4.69 13.54-.46 26.5-5.16 12.97-18.31 16.66-12.54 3.07-23-3.96-10.46-7.04-14.54-19.58-19.38-69.62-67.07-123.89-47.7-54.27-117.62-81.96V-760q0 33-23.5 56.5T520-680h-80v80q0 17-11.5 28.5T400-560h-80v80h40.77q15.46 0 25.81 10.35 10.34 10.34 10.34 25.8V-360H360L168-552q-3 18-5.5 36t-2.5 36q0 124.31 83 215.88 83 91.58 208.08 102.89 12.54 1.23 20.73 9.85Q480-142.77 480-130t-9 21.38q-9 8.62-21.54 7.39-147.61-11.93-248.54-120.12Q100-329.54 100-480Zm721.39 343.54L709.46-247.62q-19.46 12.39-41.92 20Q645.08-220 620-220q-66.92 0-113.46-46.54Q460-313.08 460-380q0-66.92 46.54-113.46Q553.08-540 620-540q66.92 0 113.46 46.54Q780-446.92 780-380q0 25.46-7.81 48.12-7.81 22.65-20.58 42.11l111.93 111.16q8.69 8.3 8.69 20.88 0 12.58-8.69 21.27-8.31 8.31-20.89 8.31-12.57 0-21.26-8.31ZM691-309q29-29 29-71t-29-71q-29-29-71-29t-71 29q-29 29-29 71t29 71q29 29 71 29t71-29Z"/></svg>',
		'desc'     => 'Collect site URL, audit focus areas, and challenges before you begin.',
		'content'  => '<!-- wp:wpzoom-forms/form -->
<div class="wp-block-wpzoom-forms-form"><!-- wp:wpzoom-forms/text-name-field {"label":"Name","required":true,"id":"input_e9882525","name":"Name"} -->
<label for="input_e9882525"><span>Name</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_e9882525" id="input_e9882525" placeholder="" required class="wp-block-wpzoom-forms-text-name-field"/>
<!-- /wp:wpzoom-forms/text-name-field -->

<!-- wp:wpzoom-forms/text-email-field {"label":"Email","required":true,"id":"input_5d24db31","name":"Email","replyto":true} -->
<label for="input_5d24db31"><span>Email</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="email" name="input_5d24db31" id="input_5d24db31" placeholder="" required data-replyto="true" class="wp-block-wpzoom-forms-text-email-field"/>
<!-- /wp:wpzoom-forms/text-email-field -->

<!-- wp:wpzoom-forms/text-website-field {"label":"Website URL","required":true,"id":"input_9bdeed34","name":"Website URL"} -->
<label for="input_9bdeed34"><span>Website URL</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="url" name="input_9bdeed34" id="input_9bdeed34" placeholder="" required class="wp-block-wpzoom-forms-text-website-field"/>
<!-- /wp:wpzoom-forms/text-website-field -->

<!-- wp:wpzoom-forms/multi-checkbox-field {"label":"Areas of Focus for Audit","options":["seo","performance","accessibility","ux"],"required":true,"id":"input_6f341a02","name":"Areas of Focus for Audit"} -->
<label for="input_6f341a02"><span>Areas of Focus for Audit</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><ul class="wp-block-wpzoom-forms-multi-checkbox-field" id="input_6f341a02"><li><label><input type="checkbox" name="input_6f341a02[]" id="input_6f341a02-0" value="seo" required/>seo</label></li><li><label><input type="checkbox" name="input_6f341a02[]" id="input_6f341a02-1" value="performance" required/>performance</label></li><li><label><input type="checkbox" name="input_6f341a02[]" id="input_6f341a02-2" value="accessibility" required/>accessibility</label></li><li><label><input type="checkbox" name="input_6f341a02[]" id="input_6f341a02-3" value="ux" required/>ux</label></li></ul>
<!-- /wp:wpzoom-forms/multi-checkbox-field -->

<!-- wp:wpzoom-forms/textarea-field {"label":"Tell us about your challenges","required":true,"id":"input_6e877ef8","name":"Tell us about your challenges"} -->
<label for="input_6e877ef8"><span>Tell us about your challenges</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><textarea name="input_6e877ef8" id="input_6e877ef8" cols="55" rows="10" placeholder="" required class="wp-block-wpzoom-forms-textarea-field"></textarea>
<!-- /wp:wpzoom-forms/textarea-field -->

<!-- wp:wpzoom-forms/submit-field {"label":"Request Audit","id":"input_submit","name":"Request Audit"} -->
<input type="submit" id="input_submit" value="Request Audit" class="wp-block-wpzoom-forms-submit-field"/>
<!-- /wp:wpzoom-forms/submit-field --></div>
<!-- /wp:wpzoom-forms/form -->',
	),
	array(
		'id'       => 'podcast-guest-pitch',
		'name'     => 'Podcast Guest Pitch',
		'category' => 'events',
		'icon'     => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#1f1f1f"><path d="M303.23-138.62q-8.61-8.61-8.61-21.38v-101.85q-90.31-10.69-153.08-73.46-62.77-62.77-74.08-150.46-2.23-13.15 6.08-23.69Q81.85-520 94.62-520q12.77 0 21.69 10.23 8.92 10.23 11.77 24 12.3 70.85 67.27 118.31Q250.31-320 324.62-320q6.53 0 12.88-.19 6.35-.19 12.5-1.58 9.92 14.62 21.27 27.69Q382.62-281 395-269.46q-10 2.61-19.69 4.3-9.7 1.7-20.7 3.31V-160q0 12.77-8.61 21.38-8.62 8.62-21.38 8.62-12.77 0-21.39-8.62Zm261.19-310.42q-29.03-29.04-29.03-70.96v-240q0-41.92 29.03-70.96Q593.46-860 635.38-860q41.93 0 70.96 29.04 29.04 29.04 29.04 70.96v240q0 41.92-29.04 70.96Q677.31-420 635.38-420q-41.92 0-70.96-29.04Zm99.46-42.46q11.5-11.5 11.5-28.5v-240q0-17-11.5-28.5t-28.5-11.5q-17 0-28.5 11.5t-11.5 28.5v240q0 17 11.5 28.5t28.5 11.5q17 0 28.5-11.5Zm-361.57 68.34q-34.16-8-55.92-34.84-21.77-26.85-21.77-62v-240q0-41.92 29.04-70.96Q282.69-860 324.62-860q41.92 0 70.96 29.04 29.03 29.04 29.03 70.96v157q-6.15-2.46-12-3.39-5.84-.92-12.61-.92-46.69 1.92-78.38 35.66-31.7 33.73-31.7 80.8 0 16.93 3.66 33.85 3.65 16.92 8.73 33.84Zm462.34 56.08q54.97-47.07 67.27-118.69 2.85-13.77 11.77-24T865.38-520q12.77 0 21.08 10.54 8.31 10.54 6.08 23.69-11.31 87.69-74.08 150.46-62.77 62.77-153.08 73.46V-160q0 12.77-8.61 21.38-8.62 8.62-21.39 8.62-12.76 0-21.38-8.62-8.61-8.61-8.61-21.38v-101.85q-90.31-10.69-153.08-73.46-62.77-62.77-74.08-150.46-2.23-13.54 6.08-23.88Q392.62-520 405.39-520q13.15 0 21.88 10.23 8.73 10.23 11.58 24 12.3 71.62 67.26 118.69Q561.08-320 635.38-320q74.31 0 129.27-47.08ZM635.38-640Z"/></svg>',
		'desc'     => 'Receive guest pitches with topic, profile link, and a short expertise summary.',
		'content'  => '<!-- wp:wpzoom-forms/form -->
<div class="wp-block-wpzoom-forms-form"><!-- wp:wpzoom-forms/text-name-field {"label":"Your Name","required":true,"id":"input_018bfbfe","name":"Your Name"} -->
<label for="input_018bfbfe"><span>Your Name</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_018bfbfe" id="input_018bfbfe" placeholder="" required class="wp-block-wpzoom-forms-text-name-field"/>
<!-- /wp:wpzoom-forms/text-name-field -->

<!-- wp:wpzoom-forms/text-email-field {"label":"Your Email","required":true,"id":"input_1e9ac516","name":"Your Email","replyto":true} -->
<label for="input_1e9ac516"><span>Your Email</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="email" name="input_1e9ac516" id="input_1e9ac516" placeholder="" required data-replyto="true" class="wp-block-wpzoom-forms-text-email-field"/>
<!-- /wp:wpzoom-forms/text-email-field -->

<!-- wp:wpzoom-forms/text-plain-field {"label":"Proposed Podcast Topic","required":true,"id":"input_e020b878","name":"Proposed Podcast Topic"} -->
<label for="input_e020b878"><span>Proposed Podcast Topic</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_e020b878" id="input_e020b878" placeholder="" required data-subject="false" class="wp-block-wpzoom-forms-text-plain-field"/>
<!-- /wp:wpzoom-forms/text-plain-field -->

<!-- wp:wpzoom-forms/text-website-field {"label":"Your Profile URL","id":"input_b0b8cdf3","name":"Your Profile URL"} -->
<label for="input_b0b8cdf3"><span>Your Profile URL</span></label><input type="url" name="input_b0b8cdf3" id="input_b0b8cdf3" placeholder="" class="wp-block-wpzoom-forms-text-website-field"/>
<!-- /wp:wpzoom-forms/text-website-field -->

<!-- wp:wpzoom-forms/textarea-field {"label":"Brief Summary of Your Expertise and Why You\'d Be a Great Guest","required":true,"id":"input_e83de6cc","name":"Brief Summary of Your Expertise and Why You\'d Be a Great Guest"} -->
<label for="input_e83de6cc"><span>Brief Summary of Your Expertise and Why You\'d Be a Great Guest</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><textarea name="input_e83de6cc" id="input_e83de6cc" cols="55" rows="10" placeholder="" required class="wp-block-wpzoom-forms-textarea-field"></textarea>
<!-- /wp:wpzoom-forms/textarea-field -->

<!-- wp:wpzoom-forms/submit-field {"label":"Submit Pitch","id":"input_submit","name":"Submit Pitch"} -->
<input type="submit" id="input_submit" value="Submit Pitch" class="wp-block-wpzoom-forms-submit-field"/>
<!-- /wp:wpzoom-forms/submit-field --></div>
<!-- /wp:wpzoom-forms/form -->',
	),
	array(
		'id'       => 'community-join-form',
		'name'     => 'Community Join Form',
		'category' => 'nonprofit',
		'icon'     => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#1f1f1f"><path d="M54.62-248.46q-14.71 0-24.67-9.95Q20-268.37 20-283.08v-14.15q0-39.92 41.69-65.58 41.7-25.65 108.7-25.65 11.07 0 22.3.69t22.62 2.69q-11.69 18.7-17.35 38.81-5.65 20.12-5.65 41.27v56.54H54.62Zm241.69 0q-15.62 0-25.96-10.4Q260-269.25 260-284.62v-18.84q0-28.09 15.77-51.35 15.77-23.27 45.46-40.57 29.69-17.31 70.16-25.96 40.46-8.66 88.46-8.66 48.92 0 89.38 8.66 40.46 8.65 70.15 25.96 29.7 17.3 45.16 40.57Q700-331.55 700-303.46v18.84q0 15.37-10.4 25.76-10.39 10.4-25.76 10.4H296.31Zm471.38 0v-56.49q0-22.59-5.34-42.51-5.35-19.92-16.04-37.62 11.77-2 22.5-2.69t21.19-.69q67 0 108.5 25.35 41.5 25.34 41.5 65.88v14.15q0 14.71-9.95 24.67-9.96 9.95-24.67 9.95H767.69Zm-444.61-60h314.46v-4.23q-6.15-24.23-51.08-40.77Q541.54-370 480-370q-61.54 0-106.46 16.54-44.93 16.54-50.46 40.77v4.23Zm-152.8-118.85q-28.28 0-48.32-20.11t-20.04-48.35q0-28.61 20.12-48.34 20.11-19.73 48.35-19.73 28.61 0 48.53 19.73 19.93 19.73 19.93 48.46 0 27.88-19.72 48.11-19.71 20.23-48.85 20.23Zm619.72 0q-28 0-48.23-20.23-20.23-20.23-20.23-48.11 0-28.73 20.23-48.46t48.3-19.73q28.93 0 48.66 19.73 19.73 19.73 19.73 48.34 0 28.24-19.68 48.35-19.68 20.11-48.78 20.11ZM480.14-460q-43.22 0-73.6-30.29-30.38-30.28-30.38-73.55 0-44.14 30.28-73.99 30.29-29.86 73.56-29.86 44.13 0 73.99 29.82 29.85 29.81 29.85 73.89 0 43.21-29.81 73.6Q524.21-460 480.14-460Zm.05-60q18.35 0 31-12.84 12.66-12.85 12.66-31.2 0-18.34-12.61-31-12.61-12.65-31.24-12.65-18.15 0-31 12.61-12.85 12.6-12.85 31.24 0 18.15 12.85 31Q461.85-520 480.19-520Zm.43 211.54ZM480-563.84Z"/></svg>',
		'desc'     => 'Ask for membership type and why someone wants to join your community.',
		'content'  => '<!-- wp:wpzoom-forms/form -->
<div class="wp-block-wpzoom-forms-form"><!-- wp:wpzoom-forms/text-name-field {"label":"Name","required":true,"id":"input_b5722f12","name":"Name"} -->
<label for="input_b5722f12"><span>Name</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_b5722f12" id="input_b5722f12" placeholder="" required class="wp-block-wpzoom-forms-text-name-field"/>
<!-- /wp:wpzoom-forms/text-name-field -->

<!-- wp:wpzoom-forms/text-email-field {"label":"Email","required":true,"id":"input_da279fba","name":"Email","replyto":true} -->
<label for="input_da279fba"><span>Email</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="email" name="input_da279fba" id="input_da279fba" placeholder="" required data-replyto="true" class="wp-block-wpzoom-forms-text-email-field"/>
<!-- /wp:wpzoom-forms/text-email-field -->

<!-- wp:wpzoom-forms/radio-field {"label":"Membership Type","options":["basic","premium","vip"],"required":true,"id":"input_c34fd934","name":"Membership Type","defaultValue":"basic"} -->
<label for="input_c34fd934"><span>Membership Type</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><ul class="wp-block-wpzoom-forms-radio-field" id="input_c34fd934"><li><label><input type="radio" name="Membership Type" id="input_c34fd934-0" value="basic" checked required/>basic</label></li><li><label><input type="radio" name="Membership Type" id="input_c34fd934-1" value="premium" required/>premium</label></li><li><label><input type="radio" name="Membership Type" id="input_c34fd934-2" value="vip" required/>vip</label></li></ul>
<!-- /wp:wpzoom-forms/radio-field -->

<!-- wp:wpzoom-forms/textarea-field {"label":"Why do you want to join?","required":true,"id":"input_133d37f9","name":"Why do you want to join?"} -->
<label for="input_133d37f9"><span>Why do you want to join?</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><textarea name="input_133d37f9" id="input_133d37f9" cols="55" rows="10" placeholder="" required class="wp-block-wpzoom-forms-textarea-field"></textarea>
<!-- /wp:wpzoom-forms/textarea-field -->

<!-- wp:wpzoom-forms/submit-field {"label":"Join Community","id":"input_submit","name":"Join Community"} -->
<input type="submit" id="input_submit" value="Join Community" class="wp-block-wpzoom-forms-submit-field"/>
<!-- /wp:wpzoom-forms/submit-field --></div>
<!-- /wp:wpzoom-forms/form -->',
	),
	array(
		'id'       => 'webinar-interest-form',
		'name'     => 'Webinar Interest',
		'category' => 'events',
		'icon'     => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#1f1f1f"><path d="M607.31-502.08 712.69-563q9.23-5.42 9.23-15.37 0-9.94-9.23-15.55l-105.38-60.93q-8.11-4.61-18.02-4.61t-18.14 4.61l-105.38 60.93q-9.23 5.42-9.23 15.36t9.23 15.56l105.38 60.92q8.11 4.62 18.02 4.62t18.14-4.62Zm0 117.93 70.77-40.39q8.13-4.5 13.1-13.06 4.97-8.57 4.97-18.48v-56.38l-88.84 51.61q-8.14 5-18.08 5-9.94 0-18.08-5l-88.84-51.61v56.38q0 9.91 4.97 18.48 4.97 8.56 13.1 13.06l70.77 40.39q8.11 4.61 18.02 4.61t18.14-4.61ZM480-480Zm380-227.69v455.38q0 29.83-21.24 51.07Q817.52-180 787.69-180H618q-12.75 0-21.38-8.63-8.62-8.63-8.62-21.38 0-12.76 8.62-21.37Q605.25-240 618-240h169.69q5.39 0 8.85-3.46t3.46-8.85v-455.38q0-5.39-3.46-8.85t-8.85-3.46H172.31q-5.39 0-8.85 3.46t-3.46 8.85v12.54q0 12.75-8.63 21.37-8.63 8.63-21.38 8.63-12.76 0-21.37-8.63-8.62-8.62-8.62-21.37v-12.54q0-29.83 21.24-51.07Q142.48-780 172.31-780h615.38q29.83 0 51.07 21.24Q860-737.52 860-707.69ZM316.08-180q-12.08 0-21-7.58-8.93-7.58-11.16-19.73-8.69-61.46-53.03-104.5-44.35-43.04-106.2-52.11-11.57-1.5-18.13-10.88-6.56-9.37-6.56-21.37 0-12.75 8.69-21.37 8.7-8.61 20.23-7 85.16 9.69 145.12 69.96 59.96 60.27 69.88 145.43 1.62 12.15-6.69 20.65-8.31 8.5-21.15 8.5Zm155.46 0q-13.16 0-21.58-9.08-8.42-9.08-10.04-22.61-11.69-124.54-99.69-211.43-88-86.88-212.92-97.19-12.15-1.23-19.73-10.23-7.58-9.01-7.58-21.01 0-12.76 8.39-21.68 8.38-8.92 20.3-7.69 149.85 10.31 254.96 115 105.12 104.69 116.66 254.53 1.23 13.16-7.4 22.27-8.62 9.12-21.37 9.12Zm-330.79 0q-17.13 0-28.94-11.83Q100-203.66 100-220.79q0-17.13 11.83-28.94 11.83-11.81 28.96-11.81 17.13 0 28.94 11.83 11.81 11.83 11.81 28.96 0 17.13-11.83 28.94Q157.88-180 140.75-180Z"/></svg>',
		'desc'     => 'Let people pick webinar topics and a preferred date, plus an optional message.',
		'content'  => '<!-- wp:wpzoom-forms/form -->
<div class="wp-block-wpzoom-forms-form"><!-- wp:wpzoom-forms/text-name-field {"label":"Name","required":true,"id":"input_40ded0b3","name":"Name"} -->
<label for="input_40ded0b3"><span>Name</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_40ded0b3" id="input_40ded0b3" placeholder="" required class="wp-block-wpzoom-forms-text-name-field"/>
<!-- /wp:wpzoom-forms/text-name-field -->

<!-- wp:wpzoom-forms/text-email-field {"label":"Email","required":true,"id":"input_ca17d021","name":"Email","replyto":true} -->
<label for="input_ca17d021"><span>Email</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="email" name="input_ca17d021" id="input_ca17d021" placeholder="" required data-replyto="true" class="wp-block-wpzoom-forms-text-email-field"/>
<!-- /wp:wpzoom-forms/text-email-field -->

<!-- wp:wpzoom-forms/multi-checkbox-field {"label":"Preferred Webinar Topics","options":["Topic 1","Topic 2","Topic 3"],"required":true,"id":"input_d1792bf3","name":"Preferred Webinar Topics"} -->
<label for="input_d1792bf3"><span>Preferred Webinar Topics</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><ul class="wp-block-wpzoom-forms-multi-checkbox-field" id="input_d1792bf3"><li><label><input type="checkbox" name="input_d1792bf3[]" id="input_d1792bf3-0" value="Topic 1" required/>Topic 1</label></li><li><label><input type="checkbox" name="input_d1792bf3[]" id="input_d1792bf3-1" value="Topic 2" required/>Topic 2</label></li><li><label><input type="checkbox" name="input_d1792bf3[]" id="input_d1792bf3-2" value="Topic 3" required/>Topic 3</label></li></ul>
<!-- /wp:wpzoom-forms/multi-checkbox-field -->

<!-- wp:wpzoom-forms/datepicker-field {"label":"Preferred Date","required":true,"id":"input_12f00fbd","name":"Preferred Date"} -->
<label for="input_12f00fbd"><span>Preferred Date</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input data-datepicker="true" autocomplete="off" data-date-format="Y-m-d" data-mode="single" type="text" name="input_12f00fbd" id="input_12f00fbd" placeholder="Y-m-d" required class="wp-block-wpzoom-forms-datepicker-field"/>
<!-- /wp:wpzoom-forms/datepicker-field -->

<!-- wp:wpzoom-forms/textarea-field {"id":"input_423ea896","name":"Message"} -->
<label for="input_423ea896"><span>Message</span></label><textarea name="input_423ea896" id="input_423ea896" cols="55" rows="10" placeholder="" class="wp-block-wpzoom-forms-textarea-field"></textarea>
<!-- /wp:wpzoom-forms/textarea-field -->

<!-- wp:wpzoom-forms/submit-field {"label":"Submit","id":"input_submit","name":"Submit"} -->
<input type="submit" id="input_submit" value="Submit" class="wp-block-wpzoom-forms-submit-field"/>
<!-- /wp:wpzoom-forms/submit-field --></div>
<!-- /wp:wpzoom-forms/form -->',
	),
	array(
		'id'       => 'beta-access-request',
		'name'     => 'Beta Access Request',
		'category' => 'support',
		'icon'     => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#1f1f1f"><path d="M200-140q-38.27 0-54.4-34.15-16.14-34.16 7.79-63.54L380-513.08V-760h-47.69q-12.75 0-21.38-8.63-8.62-8.63-8.62-21.38 0-12.76 8.62-21.37 8.63-8.62 21.38-8.62h295.38q12.75 0 21.38 8.63 8.62 8.63 8.62 21.38 0 12.76-8.62 21.37-8.63 8.62-21.38 8.62H580v246.92l226.61 275.39q23.93 29.38 7.79 63.54Q798.27-140 760-140H200Zm0-60h560L520-492v-268h-80v268L200-200Zm280-280Z"/></svg>',
		'desc'     => 'Sign up beta testers with role, optional website, and a required use case.',
		'content'  => '<!-- wp:wpzoom-forms/form -->
<div class="wp-block-wpzoom-forms-form"><!-- wp:wpzoom-forms/text-name-field {"label":"Name","required":true,"id":"input_c71caa13","name":"Name"} -->
<label for="input_c71caa13"><span>Name</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_c71caa13" id="input_c71caa13" placeholder="" required class="wp-block-wpzoom-forms-text-name-field"/>
<!-- /wp:wpzoom-forms/text-name-field -->

<!-- wp:wpzoom-forms/text-email-field {"label":"Email","required":true,"id":"input_095728b8","name":"Email","replyto":true} -->
<label for="input_095728b8"><span>Email</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="email" name="input_095728b8" id="input_095728b8" placeholder="" required data-replyto="true" class="wp-block-wpzoom-forms-text-email-field"/>
<!-- /wp:wpzoom-forms/text-email-field -->

<!-- wp:wpzoom-forms/select-field {"label":"Role","options":["Developer","Designer","Project Manager","Other"],"required":true,"id":"input_7d9a0813","name":"Role","defaultValue":"Developer"} -->
<label for="input_7d9a0813"><span>Role</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><select name="input_7d9a0813" id="input_7d9a0813" required defaultvalue="Developer" class="wp-block-wpzoom-forms-select-field"><option value="Developer">Developer</option><option value="Designer">Designer</option><option value="Project Manager">Project Manager</option><option value="Other">Other</option></select>
<!-- /wp:wpzoom-forms/select-field -->

<!-- wp:wpzoom-forms/text-website-field {"label":"Website","id":"input_7438ad1d","name":"Website"} -->
<label for="input_7438ad1d"><span>Website</span></label><input type="url" name="input_7438ad1d" id="input_7438ad1d" placeholder="" class="wp-block-wpzoom-forms-text-website-field"/>
<!-- /wp:wpzoom-forms/text-website-field -->

<!-- wp:wpzoom-forms/textarea-field {"label":"Use Case Details","required":true,"id":"input_af043c01","name":"Use Case Details"} -->
<label for="input_af043c01"><span>Use Case Details</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><textarea name="input_af043c01" id="input_af043c01" cols="55" rows="10" placeholder="" required class="wp-block-wpzoom-forms-textarea-field"></textarea>
<!-- /wp:wpzoom-forms/textarea-field -->

<!-- wp:wpzoom-forms/submit-field {"label":"Request Beta Access","id":"input_submit","name":"Request Beta Access"} -->
<input type="submit" id="input_submit" value="Request Beta Access" class="wp-block-wpzoom-forms-submit-field"/>
<!-- /wp:wpzoom-forms/submit-field --></div>
<!-- /wp:wpzoom-forms/form -->',
	),
	array(
		'id'       => 'maintenance-request-basic',
		'name'     => 'Maintenance Request',
		'category' => 'support',
		'icon'     => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#1f1f1f"><path d="M357.69-370q-95.83 0-162.91-67.08-67.09-67.09-67.09-162.92 0-16.67 2.43-33.33 2.42-16.67 8.11-31.98 3.85-9.23 10.39-13.65 6.53-4.42 14.76-6.42 8.24-2 16.58.3 8.35 2.31 15.19 9.16l106.54 105.77L387.54-656 282.15-761.77q-6.84-6.85-9.15-15.46-2.31-8.61-.31-16.77 2-8.15 6.62-14.69 4.61-6.54 13.46-10.39 15.31-6.07 31.85-8.5 16.53-2.42 33.07-2.42 95.84 0 162.92 67.08 67.08 67.09 67.08 162.92 0 25.31-4.77 47.15-4.77 21.85-14.31 42.24L786-294.46q25.15 25.05 25.15 61.33t-25.07 61.44q-25.07 25.15-61.38 25.15t-61.47-25.77L447.08-389.08q-21.16 9.16-43.01 14.12-21.86 4.96-46.38 4.96Zm0-60q26.26 0 52.52-7.81 26.25-7.8 48.02-24.42l248.39 248.39q7.3 7.3 18.3 7 11-.31 18.31-7.62 7.31-7.31 7.31-18.31t-7.31-18.31L494.85-498.85q16.84-20.77 24.84-47.27 8-26.5 8-53.88 0-66.54-47.54-117.77Q432.61-769 359.84-768l86.7 86.69q10.84 10.85 10.84 25.31 0 14.46-10.84 25.31L327-511.15q-10.85 10.84-25.31 10.84-14.46 0-25.31-10.84l-86.69-86.7q.15 77 51.77 122.43Q293.08-430 357.69-430Zm110.16-60.62Z"/></svg>',
		'desc'     => 'Log maintenance requests with subject, priority level, and issue details.',
		'content'  => '<!-- wp:wpzoom-forms/form -->
<div class="wp-block-wpzoom-forms-form"><!-- wp:wpzoom-forms/text-name-field {"label":"Your Name","required":true,"id":"input_f0c0506a","name":"Your Name"} -->
<label for="input_f0c0506a"><span>Your Name</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_f0c0506a" id="input_f0c0506a" placeholder="" required class="wp-block-wpzoom-forms-text-name-field"/>
<!-- /wp:wpzoom-forms/text-name-field -->

<!-- wp:wpzoom-forms/text-email-field {"label":"Your Email","required":true,"id":"input_474f2f70","name":"Your Email","replyto":true} -->
<label for="input_474f2f70"><span>Your Email</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="email" name="input_474f2f70" id="input_474f2f70" placeholder="" required data-replyto="true" class="wp-block-wpzoom-forms-text-email-field"/>
<!-- /wp:wpzoom-forms/text-email-field -->

<!-- wp:wpzoom-forms/text-plain-field {"label":"Subject","placeholder":"Enter Subject","required":true,"id":"input_b809f71f","name":"Subject","subject":true} -->
<label for="input_b809f71f"><span>Subject</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_b809f71f" id="input_b809f71f" placeholder="Enter Subject" required data-subject="true" class="wp-block-wpzoom-forms-text-plain-field"/>
<!-- /wp:wpzoom-forms/text-plain-field -->

<!-- wp:wpzoom-forms/radio-field {"label":"Priority","options":["Low","Medium","High"],"required":true,"id":"input_819a194b","name":"Priority","defaultValue":"Low"} -->
<label for="input_819a194b"><span>Priority</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><ul class="wp-block-wpzoom-forms-radio-field" id="input_819a194b"><li><label><input type="radio" name="Priority" id="input_819a194b-0" value="Low" checked required/>Low</label></li><li><label><input type="radio" name="Priority" id="input_819a194b-1" value="Medium" required/>Medium</label></li><li><label><input type="radio" name="Priority" id="input_819a194b-2" value="High" required/>High</label></li></ul>
<!-- /wp:wpzoom-forms/radio-field -->

<!-- wp:wpzoom-forms/textarea-field {"label":"Issue Details","placeholder":"Describe the issue in detail","required":true,"id":"input_a50b70c2","name":"Issue Details"} -->
<label for="input_a50b70c2"><span>Issue Details</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><textarea name="input_a50b70c2" id="input_a50b70c2" cols="55" rows="10" placeholder="Describe the issue in detail" required class="wp-block-wpzoom-forms-textarea-field"></textarea>
<!-- /wp:wpzoom-forms/textarea-field -->

<!-- wp:wpzoom-forms/submit-field {"label":"Submit Request","id":"input_submit","name":"Submit Request"} -->
<input type="submit" id="input_submit" value="Submit Request" class="wp-block-wpzoom-forms-submit-field"/>
<!-- /wp:wpzoom-forms/submit-field --></div>
<!-- /wp:wpzoom-forms/form -->',
	),
	array(
		'id'       => 'press-inquiry-form',
		'name'     => 'Press Inquiry',
		'category' => 'business',
		'icon'     => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#1f1f1f"><path d="M305.23-294.77q10.15-10.15 10.15-25.23t-10.15-25.23q-10.15-10.15-25.23-10.15t-25.23 10.15q-10.15 10.15-10.15 25.23t10.15 25.23q10.15 10.15 25.23 10.15t25.23-10.15Zm-46.61-366.61Q250-652.77 250-640v185.39q0 12.77 8.62 21.38 8.61 8.62 21.38 8.62t21.38-8.62q8.62-8.61 8.62-21.38V-640q0-12.77-8.62-21.38Q292.77-670 280-670t-21.38 8.62ZM680-290q12.77 0 21.38-8.62Q710-307.23 710-320t-8.62-21.38Q692.77-350 680-350H480q-12.77 0-21.38 8.62Q450-332.77 450-320t8.62 21.38Q467.23-290 480-290h200Zm0-160q12.77 0 21.38-8.62Q710-467.23 710-480t-8.62-21.38Q692.77-510 680-510H480q-12.77 0-21.38 8.62Q450-492.77 450-480t8.62 21.38Q467.23-450 480-450h200Zm0-160q12.77 0 21.38-8.62Q710-627.23 710-640t-8.62-21.38Q692.77-670 680-670H480q-12.77 0-21.38 8.62Q450-652.77 450-640t8.62 21.38Q467.23-610 480-610h200ZM172.31-140Q142-140 121-161q-21-21-21-51.31v-535.38Q100-778 121-799q21-21 51.31-21h615.38Q818-820 839-799q21 21 21 51.31v535.38Q860-182 839-161q-21 21-51.31 21H172.31Zm0-60h615.38q4.62 0 8.46-3.85 3.85-3.84 3.85-8.46v-535.38q0-4.62-3.85-8.46-3.84-3.85-8.46-3.85H172.31q-4.62 0-8.46 3.85-3.85 3.84-3.85 8.46v535.38q0 4.62 3.85 8.46 3.84 3.85 8.46 3.85ZM160-200v-560 560Z"/></svg>',
		'desc'     => 'Collect publication, subject, and questions from press or media contacts.',
		'content'  => '<!-- wp:wpzoom-forms/form -->
<div class="wp-block-wpzoom-forms-form"><!-- wp:wpzoom-forms/text-name-field {"label":"Name","required":true,"id":"input_0362219e","name":"Name"} -->
<label for="input_0362219e"><span>Name</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_0362219e" id="input_0362219e" placeholder="" required class="wp-block-wpzoom-forms-text-name-field"/>
<!-- /wp:wpzoom-forms/text-name-field -->

<!-- wp:wpzoom-forms/text-email-field {"label":"Email","required":true,"id":"input_1aa78781","name":"Email","replyto":true} -->
<label for="input_1aa78781"><span>Email</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="email" name="input_1aa78781" id="input_1aa78781" placeholder="" required data-replyto="true" class="wp-block-wpzoom-forms-text-email-field"/>
<!-- /wp:wpzoom-forms/text-email-field -->

<!-- wp:wpzoom-forms/text-plain-field {"label":"Publication","placeholder":"Name of Publication","id":"input_5043b9f2","name":"Publication"} -->
<label for="input_5043b9f2"><span>Publication</span></label><input type="text" name="input_5043b9f2" id="input_5043b9f2" placeholder="Name of Publication" data-subject="false" class="wp-block-wpzoom-forms-text-plain-field"/>
<!-- /wp:wpzoom-forms/text-plain-field -->

<!-- wp:wpzoom-forms/text-plain-field {"label":"Topic Subject","placeholder":"Subject of Inquiry","required":true,"id":"input_6a23ca9e","name":"Topic Subject","subject":true} -->
<label for="input_6a23ca9e"><span>Topic Subject</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_6a23ca9e" id="input_6a23ca9e" placeholder="Subject of Inquiry" required data-subject="true" class="wp-block-wpzoom-forms-text-plain-field"/>
<!-- /wp:wpzoom-forms/text-plain-field -->

<!-- wp:wpzoom-forms/textarea-field {"label":"Questions","placeholder":"Your Questions","required":true,"id":"input_13b3e147","name":"Questions"} -->
<label for="input_13b3e147"><span>Questions</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><textarea name="input_13b3e147" id="input_13b3e147" cols="55" rows="10" placeholder="Your Questions" required class="wp-block-wpzoom-forms-textarea-field"></textarea>
<!-- /wp:wpzoom-forms/textarea-field -->

<!-- wp:wpzoom-forms/submit-field {"label":"Submit Inquiry","id":"input_submit","name":"Submit Inquiry"} -->
<input type="submit" id="input_submit" value="Submit Inquiry" class="wp-block-wpzoom-forms-submit-field"/>
<!-- /wp:wpzoom-forms/submit-field --></div>
<!-- /wp:wpzoom-forms/form -->',
	),
	array(
		'id'       => 'content-brief-request',
		'name'     => 'Content Brief Request',
		'category' => 'business',
		'icon'     => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#1f1f1f"><path d="M210-400q-12.75 0-21.37-8.63-8.63-8.63-8.63-21.38 0-12.76 8.63-21.37Q197.25-460 210-460h220q12.75 0 21.38 8.63 8.62 8.63 8.62 21.38 0 12.76-8.62 21.37Q442.75-400 430-400H210Zm0-160q-12.75 0-21.37-8.63-8.63-8.63-8.63-21.38 0-12.76 8.63-21.37Q197.25-620 210-620h380q12.75 0 21.38 8.63 8.62 8.63 8.62 21.38 0 12.76-8.62 21.37Q602.75-560 590-560H210Zm0-160q-12.75 0-21.37-8.63-8.63-8.63-8.63-21.38 0-12.76 8.63-21.37Q197.25-780 210-780h380q12.75 0 21.38 8.63 8.62 8.63 8.62 21.38 0 12.76-8.62 21.37Q602.75-720 590-720H210Zm314.62 503.84v-54.46q0-7.06 2.61-13.68 2.62-6.62 8.23-12.24l206.31-205.31q7.46-7.46 16.11-10.5 8.65-3.03 17.3-3.03 9.43 0 18.25 3.53 8.82 3.54 16.03 10.62l37 37.38q6.46 7.47 10 16.16Q860-439 860-430.31t-3.23 17.69q-3.23 9-10.31 16.46L641.15-190.85q-5.61 5.62-12.23 8.23-6.63 2.62-13.69 2.62h-54.46q-15.37 0-25.76-10.4-10.39-10.39-10.39-25.76Zm287.69-214.15-37-37.38 37 37.38Zm-240 202.62h38l129.84-130.47-18.38-19-18.62-18.76-130.84 130.23v38Zm149.46-149.47-18.62-18.76 37 37.76-18.38-19Z"/></svg>',
		'desc'     => 'Gather content type, topic, and brief details from clients or stakeholders.',
		'content'  => '<!-- wp:wpzoom-forms/form -->
<div class="wp-block-wpzoom-forms-form"><!-- wp:wpzoom-forms/text-name-field {"label":"Client Name","required":true,"id":"input_2e5ae16f","name":"Client Name"} -->
<label for="input_2e5ae16f"><span>Client Name</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_2e5ae16f" id="input_2e5ae16f" placeholder="" required class="wp-block-wpzoom-forms-text-name-field"/>
<!-- /wp:wpzoom-forms/text-name-field -->

<!-- wp:wpzoom-forms/text-email-field {"label":"Client Email","required":true,"id":"input_2f44772b","name":"Client Email","replyto":true} -->
<label for="input_2f44772b"><span>Client Email</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="email" name="input_2f44772b" id="input_2f44772b" placeholder="" required data-replyto="true" class="wp-block-wpzoom-forms-text-email-field"/>
<!-- /wp:wpzoom-forms/text-email-field -->

<!-- wp:wpzoom-forms/select-field {"label":"Content Type","options":["blog-post","website-copy","social-media-post","email-newsletter"],"required":true,"id":"input_e5bbb1c0","name":"Content Type","defaultValue":"blog-post"} -->
<label for="input_e5bbb1c0"><span>Content Type</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><select name="input_e5bbb1c0" id="input_e5bbb1c0" required defaultvalue="blog-post" class="wp-block-wpzoom-forms-select-field"><option value="blog-post">blog-post</option><option value="website-copy">website-copy</option><option value="social-media-post">social-media-post</option><option value="email-newsletter">email-newsletter</option></select>
<!-- /wp:wpzoom-forms/select-field -->

<!-- wp:wpzoom-forms/text-plain-field {"label":"Topic","required":true,"id":"input_6d3bede6","name":"Topic"} -->
<label for="input_6d3bede6"><span>Topic</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_6d3bede6" id="input_6d3bede6" placeholder="" required data-subject="false" class="wp-block-wpzoom-forms-text-plain-field"/>
<!-- /wp:wpzoom-forms/text-plain-field -->

<!-- wp:wpzoom-forms/textarea-field {"label":"Brief Details","required":true,"id":"input_02c21ae1","name":"Brief Details"} -->
<label for="input_02c21ae1"><span>Brief Details</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><textarea name="input_02c21ae1" id="input_02c21ae1" cols="55" rows="10" placeholder="" required class="wp-block-wpzoom-forms-textarea-field"></textarea>
<!-- /wp:wpzoom-forms/textarea-field -->

<!-- wp:wpzoom-forms/submit-field {"label":"Submit Request","id":"input_submit","name":"Submit Request"} -->
<input type="submit" id="input_submit" value="Submit Request" class="wp-block-wpzoom-forms-submit-field"/>
<!-- /wp:wpzoom-forms/submit-field --></div>
<!-- /wp:wpzoom-forms/form -->',
	),
	array(
		'id'       => 'sponsorship-inquiry-lite',
		'name'     => 'Sponsorship Inquiry',
		'category' => 'events',
		'icon'     => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#1f1f1f"><path d="M480.23-130q-12.77 0-21.38-8.62-8.62-8.61-8.62-21.38v-53.69q-46.15-8.08-80.34-32.7-34.2-24.61-54.81-68.46-5.08-10.53-.12-22.38t17.35-16.92q10.54-4.46 22.07.3 11.54 4.77 17.23 15.93 17.39 33.07 44.74 50.5Q443.69-270 485.92-270q43.31 0 76.43-20.61 33.11-20.62 33.11-65.39 0-38.85-24.88-62.04-24.89-23.19-102.96-48.42-81.77-25.85-113.58-60.46-31.81-34.62-31.81-87.08 0-60.38 42.39-95.04 42.38-34.65 85.61-38.5V-800q0-12.77 8.62-21.38 8.61-8.62 21.38-8.62t21.38 8.62q8.62 8.61 8.62 21.38v52.46q36.85 4.85 64.65 21.43 27.81 16.57 46.58 43.57 6.69 9.92 2.92 21.69-3.77 11.77-16.15 17.23-10.54 4.85-21.88 1.08-11.35-3.77-20.89-14.31-14.15-16.3-33.96-25.73-19.81-9.42-49.27-9.42-45.15 0-72.58 22-27.42 22-27.42 56 0 34.92 28.08 55.85 28.07 20.92 104.38 43.84 70.92 21.54 105.85 61.39 34.92 39.84 34.92 95.69 0 65.61-42.19 101.84-42.19 36.24-103.04 42.93V-160q0 12.77-8.62 21.38Q493-130 480.23-130Z"/></svg>',
		'desc'     => 'Let sponsors share brand, tier, and goals for your event or program.',
		'content'  => '<!-- wp:wpzoom-forms/form -->
<div class="wp-block-wpzoom-forms-form"><!-- wp:wpzoom-forms/text-name-field {"label":"Contact Name","required":true,"id":"input_619ddb97","name":"Contact Name"} -->
<label for="input_619ddb97"><span>Contact Name</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_619ddb97" id="input_619ddb97" placeholder="" required class="wp-block-wpzoom-forms-text-name-field"/>
<!-- /wp:wpzoom-forms/text-name-field -->

<!-- wp:wpzoom-forms/text-email-field {"label":"Contact Email","required":true,"id":"input_fe586e35","name":"Contact Email","replyto":true} -->
<label for="input_fe586e35"><span>Contact Email</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="email" name="input_fe586e35" id="input_fe586e35" placeholder="" required data-replyto="true" class="wp-block-wpzoom-forms-text-email-field"/>
<!-- /wp:wpzoom-forms/text-email-field -->

<!-- wp:wpzoom-forms/text-plain-field {"label":"Brand","required":true,"id":"input_197feb9a","name":"Brand"} -->
<label for="input_197feb9a"><span>Brand</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_197feb9a" id="input_197feb9a" placeholder="" required data-subject="false" class="wp-block-wpzoom-forms-text-plain-field"/>
<!-- /wp:wpzoom-forms/text-plain-field -->

<!-- wp:wpzoom-forms/select-field {"label":"Sponsorship Tier","options":["Bronze","Silver","Gold","Platinum"],"required":true,"id":"input_77ccf3d6","name":"Sponsorship Tier","defaultValue":"Bronze"} -->
<label for="input_77ccf3d6"><span>Sponsorship Tier</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><select name="input_77ccf3d6" id="input_77ccf3d6" required defaultvalue="Bronze" class="wp-block-wpzoom-forms-select-field"><option value="Bronze">Bronze</option><option value="Silver">Silver</option><option value="Gold">Gold</option><option value="Platinum">Platinum</option></select>
<!-- /wp:wpzoom-forms/select-field -->

<!-- wp:wpzoom-forms/textarea-field {"label":"Goals","required":true,"id":"input_d0558510","name":"Goals"} -->
<label for="input_d0558510"><span>Goals</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><textarea name="input_d0558510" id="input_d0558510" cols="55" rows="10" placeholder="" required class="wp-block-wpzoom-forms-textarea-field"></textarea>
<!-- /wp:wpzoom-forms/textarea-field -->

<!-- wp:wpzoom-forms/submit-field {"label":"Submit Inquiry","id":"input_submit","name":"Submit Inquiry"} -->
<input type="submit" id="input_submit" value="Submit Inquiry" class="wp-block-wpzoom-forms-submit-field"/>
<!-- /wp:wpzoom-forms/submit-field --></div>
<!-- /wp:wpzoom-forms/form -->',
	),
	array(
		'id'       => 'speaker-interest-form',
		'name'     => 'Speaker Interest',
		'category' => 'events',
		'icon'     => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#1f1f1f"><path d="M905.77-600q0 66.31-23.92 125.92-23.93 59.62-64.39 107.54-9.31 10.54-22.69 11.73-13.39 1.19-23.92-9.34-9.93-9.93-9.12-23.31.81-13.39 9.5-24.54 32.31-38.77 50.35-86.92 18.04-48.16 18.04-101.08t-18.04-100.46q-18.04-47.54-50.35-86.31-9.31-11.15-9.81-24.54-.5-13.38 9.43-23.92 9.92-10.54 23.61-9.65 13.69.88 23 11.42 40.46 47.92 64.39 107.54 23.92 59.61 23.92 125.92Zm-185.08 0q0 28.54-9.23 54.96t-25.69 48.35q-8.31 11.15-21.81 11.65-13.5.5-24.04-10.04Q630-505 629.69-518.5q-.3-13.5 6.77-25.88 7.93-12.16 12.39-26.31 4.46-14.16 4.46-29.31 0-15.92-4.46-29.69-4.46-13.77-12.39-26.54-7.07-12.39-6.77-25.58.31-13.19 10.23-23.11 10.54-10.54 24.04-10.04t21.81 11.65q16.46 21.93 25.69 47.96 9.23 26.04 9.23 55.35ZM354.23-460q-57.92 0-98.96-41.04-41.04-41.04-41.04-98.96 0-57.92 41.04-98.96Q296.31-740 354.23-740q57.92 0 98.96 41.04 41.04 41.04 41.04 98.96 0 57.92-41.04 98.96Q412.15-460 354.23-460Zm-300 243.84v-28.15q0-30.31 15.46-54.88 15.46-24.58 43.16-38.04 49.84-24.85 111.54-41.5 61.69-16.65 129.84-16.65t129.85 16.65q61.69 16.65 111.53 41.5 27.7 13.46 43.16 38.04 15.46 24.57 15.46 54.88v28.15q0 25.31-17.73 43.04t-43.04 17.73H115q-25.31 0-43.04-17.73t-17.73-43.04Zm60 .77h480v-28.92q0-13.69-7.42-23.46-7.43-9.77-18.73-15.92-36.77-18.77-93.27-35.23-56.5-16.47-120.58-16.47t-120.58 16.47q-56.5 16.46-93.27 35.23-11.3 6.15-18.73 15.92-7.42 9.77-7.42 23.46v28.92Zm296.5-328.11q23.5-23.5 23.5-56.5t-23.5-56.5q-23.5-23.5-56.5-23.5t-56.5 23.5q-23.5 23.5-23.5 56.5t23.5 56.5q23.5 23.5 56.5 23.5t56.5-23.5Zm-56.5-56.5Zm0 384.61Z"/></svg>',
		'desc'     => 'Collect talk title, summary, and profile link from prospective speakers.',
		'content'  => '<!-- wp:wpzoom-forms/form -->
<div class="wp-block-wpzoom-forms-form"><!-- wp:wpzoom-forms/text-name-field {"label":"Name","required":true,"id":"input_d7292b51","name":"Name"} -->
<label for="input_d7292b51"><span>Name</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_d7292b51" id="input_d7292b51" placeholder="" required class="wp-block-wpzoom-forms-text-name-field"/>
<!-- /wp:wpzoom-forms/text-name-field -->

<!-- wp:wpzoom-forms/text-email-field {"label":"Email","required":true,"id":"input_1c448dc1","name":"Email","replyto":true} -->
<label for="input_1c448dc1"><span>Email</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="email" name="input_1c448dc1" id="input_1c448dc1" placeholder="" required data-replyto="true" class="wp-block-wpzoom-forms-text-email-field"/>
<!-- /wp:wpzoom-forms/text-email-field -->

<!-- wp:wpzoom-forms/text-plain-field {"label":"Talk Title","required":true,"id":"input_5263d4e3","name":"Talk Title"} -->
<label for="input_5263d4e3"><span>Talk Title</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_5263d4e3" id="input_5263d4e3" placeholder="" required data-subject="false" class="wp-block-wpzoom-forms-text-plain-field"/>
<!-- /wp:wpzoom-forms/text-plain-field -->

<!-- wp:wpzoom-forms/text-website-field {"label":"Profile URL","id":"input_cc47a0f4","name":"Profile URL"} -->
<label for="input_cc47a0f4"><span>Profile URL</span></label><input type="url" name="input_cc47a0f4" id="input_cc47a0f4" placeholder="" class="wp-block-wpzoom-forms-text-website-field"/>
<!-- /wp:wpzoom-forms/text-website-field -->

<!-- wp:wpzoom-forms/textarea-field {"label":"Talk Summary","required":true,"id":"input_e63a4bbe","name":"Talk Summary"} -->
<label for="input_e63a4bbe"><span>Talk Summary</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><textarea name="input_e63a4bbe" id="input_e63a4bbe" cols="55" rows="10" placeholder="" required class="wp-block-wpzoom-forms-textarea-field"></textarea>
<!-- /wp:wpzoom-forms/textarea-field -->

<!-- wp:wpzoom-forms/submit-field {"label":"Submit","id":"input_submit","name":"Submit"} -->
<input type="submit" id="input_submit" value="Submit" class="wp-block-wpzoom-forms-submit-field"/>
<!-- /wp:wpzoom-forms/submit-field --></div>
<!-- /wp:wpzoom-forms/form -->',
	),
	array(
		'id'       => 'student-interest-form',
		'name'     => 'Student Interest',
		'category' => 'education',
		'icon'     => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#1f1f1f"><path d="M257.77-286.7q-17.69-9.84-27.73-26.56T220-350.77v-173.54l-79.84-44.15q-9.85-5.62-14.46-13.67-4.62-8.04-4.62-17.88t4.62-17.87q4.61-8.04 14.46-13.66l305.4-166.4q8.13-4.52 16.67-6.64 8.54-2.11 17.77-2.11t17.77 2.11q8.54 2.12 16.69 6.63l344.92 187.72q9.23 5 14.16 13.52 4.92 8.53 4.92 18.4v236q0 12.75-8.63 21.38-8.63 8.62-21.38 8.62-12.76 0-21.37-8.62-8.62-8.63-8.62-21.38v-224.46L740-524.31v173.54q0 20.79-10.04 37.51t-27.73 26.56L514.54-185.23q-8.23 4.61-16.77 6.73-8.54 2.11-17.77 2.11t-17.77-2.11q-8.54-2.12-16.77-6.73L257.77-286.7Zm217.61-167.61q2.7 1.54 4.81 1.54 2.12 0 4.81-1.54L753.62-600 485-745.31q-2.69-1.54-4.81-1.54-2.11 0-4.81 1.54L206.38-600l269 145.69ZM475-237.16q2.69 1.54 5 1.54t5-1.54l189.23-102.23q3.08-1.92 4.42-4.42 1.35-2.5 1.35-6.35v-142.15l-164.85 90.54q-8.23 4.61-17.07 6.73-8.85 2.12-18.08 2.12-9.23 0-18.08-2.12-8.84-2.12-17.07-6.73L280-492.31v142.15q0 3.08 1.35 5.97 1.34 2.88 4.42 4.8L475-237.16Zm5-215.22Zm0 94.07Zm0 0Z"/></svg>',
		'desc'     => 'Capture program interest plus optional start date and learning goals.',
		'content'  => '<!-- wp:wpzoom-forms/form -->
<div class="wp-block-wpzoom-forms-form"><!-- wp:wpzoom-forms/text-name-field {"label":"Student Name","required":true,"id":"input_9e9a18d0","name":"Student Name"} -->
<label for="input_9e9a18d0"><span>Student Name</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_9e9a18d0" id="input_9e9a18d0" placeholder="" required class="wp-block-wpzoom-forms-text-name-field"/>
<!-- /wp:wpzoom-forms/text-name-field -->

<!-- wp:wpzoom-forms/text-email-field {"label":"Email Address","required":true,"id":"input_7ce80207","name":"Email Address","replyto":true} -->
<label for="input_7ce80207"><span>Email Address</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="email" name="input_7ce80207" id="input_7ce80207" placeholder="" required data-replyto="true" class="wp-block-wpzoom-forms-text-email-field"/>
<!-- /wp:wpzoom-forms/text-email-field -->

<!-- wp:wpzoom-forms/select-field {"label":"Program of Interest","options":["web-development","graphic-design","digital-marketing"],"required":true,"id":"input_97a7af65","name":"Program of Interest","defaultValue":"web-development"} -->
<label for="input_97a7af65"><span>Program of Interest</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><select name="input_97a7af65" id="input_97a7af65" required defaultvalue="web-development" class="wp-block-wpzoom-forms-select-field"><option value="web-development">web-development</option><option value="graphic-design">graphic-design</option><option value="digital-marketing">digital-marketing</option></select>
<!-- /wp:wpzoom-forms/select-field -->

<!-- wp:wpzoom-forms/datepicker-field {"label":"Preferred Start Date","required":false,"id":"input_0f6808a2","name":"Preferred Start Date"} -->
<label for="input_0f6808a2"><span>Preferred Start Date</span></label><input data-datepicker="true" autocomplete="off" data-date-format="Y-m-d" data-mode="single" type="text" name="input_0f6808a2" id="input_0f6808a2" placeholder="Y-m-d" class="wp-block-wpzoom-forms-datepicker-field"/>
<!-- /wp:wpzoom-forms/datepicker-field -->

<!-- wp:wpzoom-forms/textarea-field {"label":"Learning Goals","required":false,"id":"input_9c305f5f","name":"Learning Goals"} -->
<label for="input_9c305f5f"><span>Learning Goals</span></label><textarea name="input_9c305f5f" id="input_9c305f5f" cols="55" rows="10" placeholder="" class="wp-block-wpzoom-forms-textarea-field"></textarea>
<!-- /wp:wpzoom-forms/textarea-field -->

<!-- wp:wpzoom-forms/submit-field {"label":"Submit","id":"input_submit","name":"Submit"} -->
<input type="submit" id="input_submit" value="Submit" class="wp-block-wpzoom-forms-submit-field"/>
<!-- /wp:wpzoom-forms/submit-field --></div>
<!-- /wp:wpzoom-forms/form -->',
	),
	array(
		'id'       => 'property-lead-basic',
		'name'     => 'Property Lead',
		'category' => 'real-estate',
		'icon'     => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#1f1f1f"><path d="M554.92-135.92q1.93.77 3.46.77 1.54 0 3.47-.77l235.3-72.85q-3.07-13.61-12.69-20.88t-21.77-7.27H566.8q-26.18 0-44.49-2-18.31-2-37.62-8.77l-62.77-20.54q-12.1-3.85-17.39-15.38-5.3-11.54-1.45-23.5 3.84-11.97 14.88-17.54 11.04-5.58 23.19-1.73l52 18.23q18.16 5.77 40.62 8.19 22.46 2.42 58.22 3.04h10.7q0-14.85-6.69-25.62-6.69-10.77-17.62-14.54l-232.07-85.23q-1.16-.38-2.12-.57-.96-.2-2.11-.2h-74v206.16l286.84 81Zm-15.61 58.77-271.23-77.77q-6.85 24.84-27.85 39.88-21 15.04-44.46 15.04h-55.38q-29.83 0-51.07-21.24-21.24-21.24-21.24-51.07v-238.46q0-29.82 21.24-51.06 21.24-21.24 51.07-21.24h201.24q6.29 0 12.75 1.38 6.47 1.39 12 3.23l233.08 85.85q27.23 10.07 45.23 35.65 18 25.58 18 60.04h100q43.08 0 70.19 27.81Q860-241.31 860-196.92q0 17-9 27.38-9 10.39-27.23 16.46L581.08-77.77q-9.85 3.23-20.7 3.42-10.84.2-21.07-2.8Zm-411.23-95.16q0 5.39 3.46 8.85t8.85 3.46h55.38q5.38 0 8.85-2.88 3.46-2.89 3.46-9.43v-250.77h-67.69q-5.39 0-8.85 3.47-3.46 3.46-3.46 8.84v238.46Zm443.3-684.84q10.54 3.31 20.39 10.15l197.69 141.08q14.46 9.84 22.5 25.5 8.04 15.65 8.04 33.34v232.47q0 12.75-8.63 21.37-8.63 8.63-21.38 8.63-12.76 0-21.37-8.63-8.62-8.62-8.62-21.37v-233.47q0-3.07-1.35-5.77-1.34-2.69-4.04-4.61L556.92-796.54q-3.07-2.31-6.92-2.31t-6.92 2.31l-197.7 138.08q-2.69 1.92-4.04 4.61-1.34 2.7-1.34 5.77v47.31q0 12.75-8.63 21.38-8.63 8.62-21.38 8.62-12.76 0-21.37-8.62-8.62-8.63-8.62-21.38v-46.31q0-17.69 8.04-33.34 8.04-15.66 22.5-25.5L508.23-847q9.85-6.84 20.31-10.15 10.47-3.31 21.39-3.31t21.45 3.31ZM550-789.23Zm-27.35 152.65q5.43-5.42 5.43-12.65t-5.43-12.65q-5.42-5.43-12.65-5.43t-12.65 5.43q-5.43 5.42-5.43 12.65t5.43 12.65q5.42 5.43 12.65 5.43t12.65-5.43Zm80 0q5.43-5.42 5.43-12.65t-5.43-12.65q-5.42-5.43-12.65-5.43t-12.65 5.43q-5.43 5.42-5.43 12.65t5.43 12.65q5.42 5.43 12.65 5.43t12.65-5.43Zm-80 80q5.43-5.42 5.43-12.65t-5.43-12.65q-5.42-5.43-12.65-5.43t-12.65 5.43q-5.43 5.42-5.43 12.65t5.43 12.65q5.42 5.43 12.65 5.43t12.65-5.43Zm80 0q5.43-5.42 5.43-12.65t-5.43-12.65q-5.42-5.43-12.65-5.43t-12.65 5.43q-5.43 5.42-5.43 12.65t5.43 12.65q5.42 5.43 12.65 5.43t12.65-5.43Z"/></svg>',
		'desc'     => 'Capture property type, contact info, and requirements from interested buyers.',
		'content'  => '<!-- wp:wpzoom-forms/form -->
<div class="wp-block-wpzoom-forms-form"><!-- wp:wpzoom-forms/text-name-field {"label":"Name","required":true,"id":"input_cf28f17d","name":"Name"} -->
<label for="input_cf28f17d"><span>Name</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_cf28f17d" id="input_cf28f17d" placeholder="" required class="wp-block-wpzoom-forms-text-name-field"/>
<!-- /wp:wpzoom-forms/text-name-field -->

<!-- wp:wpzoom-forms/text-email-field {"label":"Email","required":true,"id":"input_9ca41a44","name":"Email","replyto":true} -->
<label for="input_9ca41a44"><span>Email</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="email" name="input_9ca41a44" id="input_9ca41a44" placeholder="" required data-replyto="true" class="wp-block-wpzoom-forms-text-email-field"/>
<!-- /wp:wpzoom-forms/text-email-field -->

<!-- wp:wpzoom-forms/text-phone-field {"label":"Phone","id":"input_90f131ab","name":"Phone"} -->
<label for="input_90f131ab"><span>Phone</span></label><input type="tel" name="input_90f131ab" id="input_90f131ab" placeholder="" class="wp-block-wpzoom-forms-text-phone-field"/>
<!-- /wp:wpzoom-forms/text-phone-field -->

<!-- wp:wpzoom-forms/select-field {"label":"Property Type","options":["Apartment","House","Condo","Townhouse"],"required":true,"id":"input_2ac105c5","name":"Property Type","defaultValue":"Apartment"} -->
<label for="input_2ac105c5"><span>Property Type</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><select name="input_2ac105c5" id="input_2ac105c5" required defaultvalue="Apartment" class="wp-block-wpzoom-forms-select-field"><option value="Apartment">Apartment</option><option value="House">House</option><option value="Condo">Condo</option><option value="Townhouse">Townhouse</option></select>
<!-- /wp:wpzoom-forms/select-field -->

<!-- wp:wpzoom-forms/textarea-field {"label":"Requirements","id":"input_6df58d20","name":"Requirements"} -->
<label for="input_6df58d20"><span>Requirements</span></label><textarea name="input_6df58d20" id="input_6df58d20" cols="55" rows="10" placeholder="" class="wp-block-wpzoom-forms-textarea-field"></textarea>
<!-- /wp:wpzoom-forms/textarea-field -->

<!-- wp:wpzoom-forms/submit-field {"label":"Submit","id":"input_submit","name":"Submit"} -->
<input type="submit" id="input_submit" value="Submit" class="wp-block-wpzoom-forms-submit-field"/>
<!-- /wp:wpzoom-forms/submit-field --></div>
<!-- /wp:wpzoom-forms/form -->',
	),
	array(
		'id'       => 'customer-intake-short',
		'name'     => 'Customer Intake',
		'category' => 'support',
		'icon'     => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#1f1f1f"><path d="M485.39-140q-12.77 0-21.39-8.62-8.61-8.61-8.61-21.38t8.61-21.38q8.62-8.62 21.39-8.62h262.3q5 0 8.66-3.08 3.65-3.07 3.65-8.07v-279.77q0-114.7-82.08-193.12-82.07-78.42-197.92-78.42t-197.92 78.42Q200-605.62 200-490.92v205.53q0 12.77-8.62 21.39-8.61 8.61-21.38 8.61-28.77 0-49.38-20.03Q100-295.46 100-324.23v-77.69q0-19.46 11.08-35.66 11.08-16.19 28.92-26.03l1.85-51.08q4.92-65.31 33.92-121t74.38-96.96q45.39-41.27 104.77-64.31Q414.31-820 480-820t124.77 23.04q59.08 23.04 104.77 64t74.38 96.65q28.69 55.7 34.23 121l1.85 50.08q17.46 8.23 28.73 23.54Q860-426.38 860-407.54v89.31q0 18.84-11.27 34.15-11.27 15.31-28.73 23.54v49.39q0 29.53-21.19 50.34Q777.61-140 747.69-140h-262.3ZM342.65-419.19q-10.34-9.96-10.34-24.66 0-14.69 10.34-24.84 10.35-10.16 25.04-10.16 14.7 0 25.04 10.16 10.35 10.15 10.35 24.84 0 14.7-10.35 24.66-10.34 9.96-25.04 9.96-14.69 0-25.04-9.96Zm224.62 0q-10.35-9.96-10.35-24.66 0-14.69 10.35-24.84 10.34-10.16 25.04-10.16 14.69 0 25.04 10.16 10.34 10.15 10.34 24.84 0 14.7-10.34 24.66-10.35 9.96-25.04 9.96-14.7 0-25.04-9.96ZM254.85-472q-6.23-97.92 60.92-167.58 67.15-69.65 166.23-69.65 83.23 0 146.88 51.5 63.66 51.5 77.27 133.34-85.23-1-157.5-44.76-72.27-43.77-110.96-120-15.23 74.61-63.84 131.92-48.62 57.31-119 85.23Z"/></svg>',
		'desc'     => 'Quick intake for new or returning customers and what they need help with.',
		'content'  => '<!-- wp:wpzoom-forms/form -->
<div class="wp-block-wpzoom-forms-form"><!-- wp:wpzoom-forms/text-name-field {"label":"Full Name","required":true,"id":"input_b8d4fc29","name":"Full Name"} -->
<label for="input_b8d4fc29"><span>Full Name</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_b8d4fc29" id="input_b8d4fc29" placeholder="" required class="wp-block-wpzoom-forms-text-name-field"/>
<!-- /wp:wpzoom-forms/text-name-field -->

<!-- wp:wpzoom-forms/text-email-field {"label":"Email Address","required":true,"id":"input_9cb47fa5","name":"Email Address","replyto":true} -->
<label for="input_9cb47fa5"><span>Email Address</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="email" name="input_9cb47fa5" id="input_9cb47fa5" placeholder="" required data-replyto="true" class="wp-block-wpzoom-forms-text-email-field"/>
<!-- /wp:wpzoom-forms/text-email-field -->

<!-- wp:wpzoom-forms/text-phone-field {"label":"Phone Number","id":"input_ae6e6685","name":"Phone Number"} -->
<label for="input_ae6e6685"><span>Phone Number</span></label><input type="tel" name="input_ae6e6685" id="input_ae6e6685" placeholder="" class="wp-block-wpzoom-forms-text-phone-field"/>
<!-- /wp:wpzoom-forms/text-phone-field -->

<!-- wp:wpzoom-forms/radio-field {"label":"Customer Type","options":["new","existing"],"required":true,"id":"input_de8f680d","name":"Customer Type","defaultValue":"new"} -->
<label for="input_de8f680d"><span>Customer Type</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><ul class="wp-block-wpzoom-forms-radio-field" id="input_de8f680d"><li><label><input type="radio" name="Customer Type" id="input_de8f680d-0" value="new" checked required/>new</label></li><li><label><input type="radio" name="Customer Type" id="input_de8f680d-1" value="existing" required/>existing</label></li></ul>
<!-- /wp:wpzoom-forms/radio-field -->

<!-- wp:wpzoom-forms/textarea-field {"label":"How can we help?","required":true,"id":"input_54d1ae04","name":"How can we help?"} -->
<label for="input_54d1ae04"><span>How can we help?</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><textarea name="input_54d1ae04" id="input_54d1ae04" cols="55" rows="10" placeholder="" required class="wp-block-wpzoom-forms-textarea-field"></textarea>
<!-- /wp:wpzoom-forms/textarea-field -->

<!-- wp:wpzoom-forms/submit-field {"label":"Submit","id":"input_submit","name":"Submit"} -->
<input type="submit" id="input_submit" value="Submit" class="wp-block-wpzoom-forms-submit-field"/>
<!-- /wp:wpzoom-forms/submit-field --></div>
<!-- /wp:wpzoom-forms/form -->',
	),
	array(
		'id'       => 'local-service-enquiry',
		'name'     => 'Local Service Enquiry',
		'category' => 'business',
		'icon'     => '<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#1f1f1f"><path d="M480-405.39ZM100-216.16v-342.3q0-30.31 21-51.31 21-21 51.31-21H290v-76.92Q290-738 311-759q21-21 51.31-21h235.38Q628-780 649-759q21 21 21 51.31v76.92h117.69q30.31 0 51.31 21 21 21 21 51.31v342.3q0 15.37-10.4 25.76-10.39 10.4-25.76 10.4H136.16q-15.37 0-25.76-10.4-10.4-10.39-10.4-25.76Zm207.69-159.23v10q0 12.75-8.63 21.38-8.63 8.62-21.38 8.62-12.76 0-21.37-8.62-8.62-8.63-8.62-21.38v-10H160V-240h640v-135.39h-87.69v10q0 12.75-8.63 21.38-8.63 8.62-21.39 8.62-12.75 0-21.37-8.62-8.61-8.63-8.61-21.38v-10H307.69ZM160-558.46v123.08h87.69v-10.01q0-12.74 8.63-21.37 8.63-8.62 21.39-8.62 12.75 0 21.37 8.62 8.61 8.63 8.61 21.37v10.01h344.62v-10.01q0-12.74 8.63-21.37 8.63-8.62 21.38-8.62 12.76 0 21.37 8.62 8.62 8.63 8.62 21.37v10.01H800v-123.08q0-4.62-3.85-8.46-3.84-3.85-8.46-3.85H172.31q-4.62 0-8.46 3.85-3.85 3.84-3.85 8.46Zm190-72.31h260v-76.92q0-4.62-3.85-8.46-3.84-3.85-8.46-3.85H362.31q-4.62 0-8.46 3.85-3.85 3.84-3.85 8.46v76.92Z"/></svg>',
		'desc'     => 'Collect location, preferred date, and job details for local service calls.',
		'content'  => '<!-- wp:wpzoom-forms/form -->
<div class="wp-block-wpzoom-forms-form"><!-- wp:wpzoom-forms/text-name-field {"label":"Name","required":true,"id":"input_98403d1d","name":"Name"} -->
<label for="input_98403d1d"><span>Name</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_98403d1d" id="input_98403d1d" placeholder="" required class="wp-block-wpzoom-forms-text-name-field"/>
<!-- /wp:wpzoom-forms/text-name-field -->

<!-- wp:wpzoom-forms/text-email-field {"label":"Email","required":true,"id":"input_6f8d920a","name":"Email","replyto":true} -->
<label for="input_6f8d920a"><span>Email</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="email" name="input_6f8d920a" id="input_6f8d920a" placeholder="" required data-replyto="true" class="wp-block-wpzoom-forms-text-email-field"/>
<!-- /wp:wpzoom-forms/text-email-field -->

<!-- wp:wpzoom-forms/text-phone-field {"label":"Phone","id":"input_d9c2952e","name":"Phone"} -->
<label for="input_d9c2952e"><span>Phone</span></label><input type="tel" name="input_d9c2952e" id="input_d9c2952e" placeholder="" class="wp-block-wpzoom-forms-text-phone-field"/>
<!-- /wp:wpzoom-forms/text-phone-field -->

<!-- wp:wpzoom-forms/text-plain-field {"label":"Location","required":true,"id":"input_fa2ea51d","name":"Location"} -->
<label for="input_fa2ea51d"><span>Location</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_fa2ea51d" id="input_fa2ea51d" placeholder="" required data-subject="false" class="wp-block-wpzoom-forms-text-plain-field"/>
<!-- /wp:wpzoom-forms/text-plain-field -->

<!-- wp:wpzoom-forms/datepicker-field {"label":"Preferred Service Date","id":"input_376d4126","name":"Preferred Service Date"} -->
<label for="input_376d4126"><span>Preferred Service Date</span></label><input data-datepicker="true" autocomplete="off" data-date-format="Y-m-d" data-mode="single" type="text" name="input_376d4126" id="input_376d4126" placeholder="Y-m-d" class="wp-block-wpzoom-forms-datepicker-field"/>
<!-- /wp:wpzoom-forms/datepicker-field -->

<!-- wp:wpzoom-forms/textarea-field {"label":"Details","required":true,"id":"input_c41010c4","name":"Details"} -->
<label for="input_c41010c4"><span>Details</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><textarea name="input_c41010c4" id="input_c41010c4" cols="55" rows="10" placeholder="" required class="wp-block-wpzoom-forms-textarea-field"></textarea>
<!-- /wp:wpzoom-forms/textarea-field -->

<!-- wp:wpzoom-forms/submit-field {"label":"Submit","id":"input_submit","name":"Submit"} -->
<input type="submit" id="input_submit" value="Submit" class="wp-block-wpzoom-forms-submit-field"/>
<!-- /wp:wpzoom-forms/submit-field --></div>
<!-- /wp:wpzoom-forms/form -->',
	),
	array(
		'id'      => 'registration-form',
		'name'    => 'Event RSVP',
		
		'icon'    => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M14 17.25C14.4142 17.25 14.75 17.5858 14.75 18C14.75 18.4142 14.4142 18.75 14 18.75H10C9.58579 18.75 9.25 18.4142 9.25 18C9.25 17.5858 9.58579 17.25 10 17.25H14Z"  />
<path d="M16 14.25C16.4142 14.25 16.75 14.5858 16.75 15C16.75 15.4142 16.4142 15.75 16 15.75H8C7.58579 15.75 7.25 15.4142 7.25 15C7.25 14.5858 7.58579 14.25 8 14.25H16Z"  />
<path fill-rule="evenodd" clip-rule="evenodd" d="M13.2324 5.25C14.7708 5.25024 15.7499 6.58652 15.75 7.79785C15.75 8.45045 15.4781 9.02333 15.1465 9.48145C14.8135 9.94135 14.3846 10.3356 13.9814 10.6504C13.5748 10.9679 13.1702 11.2231 12.8643 11.3984C12.7108 11.4864 12.5785 11.556 12.4795 11.6055C12.4306 11.6299 12.3858 11.6513 12.3486 11.668C12.331 11.6758 12.3088 11.6861 12.2852 11.6953C12.2741 11.6996 12.2534 11.7071 12.2285 11.7148C12.2163 11.7187 12.1949 11.7253 12.168 11.7314C12.1514 11.7352 12.0855 11.75 12 11.75C11.9145 11.75 11.8486 11.7352 11.832 11.7314C11.8051 11.7253 11.7837 11.7187 11.7715 11.7148C11.7466 11.7071 11.7259 11.6996 11.7148 11.6953C11.6912 11.6861 11.669 11.6758 11.6514 11.668C11.6142 11.6513 11.5694 11.6299 11.5205 11.6055C11.4215 11.556 11.2892 11.4864 11.1357 11.3984C10.8298 11.2231 10.4252 10.9679 10.0186 10.6504C9.61537 10.3356 9.18646 9.94135 8.85352 9.48145C8.52192 9.02333 8.25 8.45045 8.25 7.79785C8.25008 6.58652 9.22922 5.25024 10.7676 5.25C11.2722 5.25 11.6803 5.39279 12 5.58203C12.3197 5.39279 12.7278 5.25 13.2324 5.25ZM13.2324 6.75C12.8896 6.75 12.6804 6.9022 12.5537 7.04102C12.4116 7.19649 12.2106 7.28516 12 7.28516C11.7894 7.28516 11.5884 7.19649 11.4463 7.04102C11.3196 6.9022 11.1104 6.75 10.7676 6.75C10.1905 6.75026 9.75008 7.27551 9.75 7.79785C9.75 8.02573 9.84595 8.29425 10.0684 8.60156C10.2894 8.90696 10.6015 9.20233 10.9414 9.46777C11.278 9.7306 11.6203 9.94682 11.8818 10.0967C11.9233 10.1204 11.9636 10.1402 12 10.1602C12.0364 10.1402 12.0767 10.1204 12.1182 10.0967C12.3797 9.94682 12.722 9.7306 13.0586 9.46777C13.3985 9.20233 13.7106 8.90696 13.9316 8.60156C14.1541 8.29425 14.25 8.02573 14.25 7.79785C14.2499 7.27551 13.8095 6.75026 13.2324 6.75Z"  />
<path fill-rule="evenodd" clip-rule="evenodd" d="M19 1.25C20.5192 1.25 21.75 2.48079 21.75 4V20C21.75 21.5192 20.5192 22.75 19 22.75H5C3.48079 22.75 2.25 21.5192 2.25 20V4C2.25 2.48079 3.48079 1.25 5 1.25H19ZM5 2.75C4.30921 2.75 3.75 3.30921 3.75 4V20C3.75 20.6908 4.30921 21.25 5 21.25H19C19.6908 21.25 20.25 20.6908 20.25 20V4C20.25 3.30921 19.6908 2.75 19 2.75H5Z"  />
</svg>
',

		'desc'    => 'An RSVP form for events like weddings or parties.',
		'content' => '',
		'is_pro'   => true,
	),
	array(
		'id'      => 'appointment-form',
		'name'    => 'Appointment Request',
		
		'icon'    =>  '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M7.11523 15.9844C7.61864 16.0356 8.01367 16.4601 8.01367 16.9795C8.01341 17.5315 7.56572 17.9795 7.01367 17.9795C6.49623 17.9795 6.07012 17.5859 6.01855 17.082L6.01367 16.9795C6.0126 16.4245 6.46333 15.9795 7.0127 15.9795L7.11523 15.9844Z"  />
<path d="M12.1152 15.9844C12.6186 16.0356 13.0137 16.4601 13.0137 16.9795C13.0134 17.5315 12.5657 17.9795 12.0137 17.9795C11.4962 17.9795 11.0701 17.5859 11.0186 17.082L11.0137 16.9795C11.0126 16.4245 11.4633 15.9795 12.0127 15.9795L12.1152 15.9844Z"  />
<path d="M12.1152 11.9844C12.6186 12.0356 13.0137 12.4601 13.0137 12.9795C13.0134 13.5315 12.5657 13.9795 12.0137 13.9795C11.4962 13.9795 11.0701 13.5859 11.0186 13.082L11.0137 12.9795C11.0126 12.4245 11.4633 11.9795 12.0127 11.9795L12.1152 11.9844Z"  />
<path d="M17.1152 11.9844C17.6186 12.0356 18.0137 12.4601 18.0137 12.9795C18.0134 13.5315 17.5657 13.9795 17.0137 13.9795C16.4962 13.9795 16.0701 13.5859 16.0186 13.082L16.0137 12.9795C16.0126 12.4245 16.4633 11.9795 17.0127 11.9795L17.1152 11.9844Z"  />
<path fill-rule="evenodd" clip-rule="evenodd" d="M16 1.25C16.4142 1.25 16.75 1.58579 16.75 2V3.25H19C20.5192 3.25 21.75 4.48079 21.75 6V19C21.75 20.5192 20.5192 21.75 19 21.75H5C3.48079 21.75 2.25 20.5192 2.25 19V6C2.25 4.48079 3.48079 3.25 5 3.25H7.25V2C7.25 1.58579 7.58579 1.25 8 1.25C8.41421 1.25 8.75 1.58579 8.75 2V3.25H15.25V2C15.25 1.58579 15.5858 1.25 16 1.25ZM3.75 19C3.75 19.6908 4.30921 20.25 5 20.25H19C19.6908 20.25 20.25 19.6908 20.25 19V9.75H3.75V19ZM5 4.75C4.30921 4.75 3.75 5.30921 3.75 6V8.25H20.25V6C20.25 5.30921 19.6908 4.75 19 4.75H16.75V6C16.75 6.41421 16.4142 6.75 16 6.75C15.5858 6.75 15.25 6.41421 15.25 6V4.75H8.75V6C8.75 6.41421 8.41421 6.75 8 6.75C7.58579 6.75 7.25 6.41421 7.25 6V4.75H5Z"  />
</svg>',

		'desc'    => 'Let customers request appointments. Ideal for salons or service-based businesses.',
		'content' => '',
		'is_pro'  => true,
	),
	array(
		'id'      => 'file-upload',
		'name'    => 'File Upload',
		
		'icon'    =>  '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M9.68652 3.26367C10.1821 3.32522 10.632 3.59626 10.917 4.01465L12.3223 6.08008L12.3623 6.12695C12.4076 6.16735 12.4672 6.19043 12.5293 6.19043H19L19.2812 6.2041C20.6679 6.345 21.7499 7.51663 21.75 8.94043V18L21.7363 18.2715C21.674 18.9012 21.3957 19.493 20.9443 19.9443C20.493 20.3957 19.9012 20.674 19.2715 20.7363L19 20.75H16C15.5858 20.75 15.25 20.4142 15.25 20C15.25 19.5858 15.5858 19.25 16 19.25H19L19.124 19.2441C19.4101 19.2157 19.6787 19.0888 19.8838 18.8838C20.0888 18.6787 20.2157 18.4101 20.2441 18.124L20.25 18V8.94043C20.25 8.29326 19.7582 7.76039 19.1279 7.69629L19 7.69043H12.5293C12.0221 7.69041 11.5437 7.46991 11.2139 7.09375L11.082 6.92383L9.67676 4.85938C9.6419 4.80819 9.58998 4.77251 9.53125 4.75781L9.4707 4.75H5C4.66851 4.74991 4.35061 4.88181 4.11621 5.11621C3.88181 5.35061 3.74991 5.66851 3.75 6V18L3.75586 18.124C3.78425 18.4101 3.91117 18.6787 4.11621 18.8838C4.35061 19.1182 4.66851 19.2501 5 19.25H8C8.41421 19.25 8.75 19.5858 8.75 20C8.75 20.4142 8.41421 20.75 8 20.75H5C4.27061 20.7501 3.57142 20.4601 3.05566 19.9443C2.6043 19.493 2.32601 18.9012 2.26367 18.2715L2.25 18V6C2.24985 5.27061 2.53991 4.57142 3.05566 4.05566C3.57142 3.53991 4.27061 3.24985 5 3.25H9.4707L9.68652 3.26367Z" fill="black"/>
<path d="M12.0254 13.251C12.0319 13.2512 12.0384 13.2516 12.0449 13.252C12.0839 13.2543 12.122 13.2595 12.1592 13.2676C12.1925 13.2748 12.2246 13.2861 12.2568 13.2979C12.2693 13.3024 12.2828 13.3044 12.2949 13.3096C12.314 13.3177 12.3311 13.33 12.3496 13.3398C12.3734 13.3525 12.3977 13.3639 12.4199 13.3789C12.4588 13.4052 12.4959 13.4353 12.5303 13.4697L14.5303 15.4697L14.582 15.5264C14.8223 15.8209 14.8049 16.2557 14.5303 16.5303C14.2557 16.8049 13.8209 16.8223 13.5264 16.582L13.4697 16.5303L12.75 15.8105V20C12.75 20.4142 12.4142 20.75 12 20.75C11.5858 20.75 11.25 20.4142 11.25 20V15.8105L10.5303 16.5303C10.2374 16.8232 9.76262 16.8232 9.46973 16.5303C9.17683 16.2374 9.17683 15.7626 9.46973 15.4697L11.4697 13.4697L11.5264 13.418C11.5366 13.4097 11.5481 13.4032 11.5586 13.3955C11.5741 13.3841 11.59 13.3734 11.6064 13.3633C11.6303 13.3485 11.6546 13.3351 11.6797 13.3232C11.6979 13.3146 11.7164 13.307 11.7354 13.2998C11.7629 13.2894 11.7909 13.2814 11.8193 13.2744C11.8394 13.2694 11.8592 13.2631 11.8799 13.2598C11.8909 13.258 11.902 13.2562 11.9131 13.2549C11.9416 13.2516 11.9706 13.25 12 13.25C12.0085 13.25 12.017 13.2507 12.0254 13.251Z" fill="black"/>
</svg>',

		'desc'    => 'Allow users to send you documents, images, or other files through a simple upload form.',
		'content' => '',
		'is_pro'  => true,
	),
	array(
		'id'      => 'job-application',
		'name'    => 'Job Application',
		
		'icon'    =>  '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M18.7197 16.8564C19.0126 16.5637 19.4874 16.5638 19.7803 16.8564C20.0731 17.1493 20.0731 17.6241 19.7803 17.917L18.2803 19.417C18.0057 19.6916 17.5709 19.7091 17.2764 19.4688L17.2197 19.417L16.2197 18.417L16.168 18.3604C15.9279 18.0658 15.9452 17.631 16.2197 17.3564C16.4943 17.082 16.9291 17.0646 17.2236 17.3047L17.2803 17.3564L17.75 17.8262L18.7197 16.8564Z" fill="black"/>
<path fill-rule="evenodd" clip-rule="evenodd" d="M18 13.25C20.6234 13.25 22.75 15.3766 22.75 18C22.75 20.6234 20.6234 22.75 18 22.75C15.3766 22.75 13.25 20.6234 13.25 18C13.25 15.3766 15.3766 13.25 18 13.25ZM18 14.75C16.2051 14.75 14.75 16.2051 14.75 18C14.75 19.7949 16.2051 21.25 18 21.25C19.7949 21.25 21.25 19.7949 21.25 18C21.25 16.2051 19.7949 14.75 18 14.75Z" fill="black"/>
<path fill-rule="evenodd" clip-rule="evenodd" d="M13.5 2.25C15.0188 2.25 16.25 3.48122 16.25 5V6.25H18.5C20.2949 6.25 21.75 7.70507 21.75 9.5V11C21.75 11.4142 21.4142 11.75 21 11.75C20.5858 11.75 20.25 11.4142 20.25 11V9.5C20.25 8.5335 19.4665 7.75 18.5 7.75H5.5C4.5335 7.75 3.75 8.5335 3.75 9.5V12.25H12C12.4142 12.25 12.75 12.5858 12.75 13C12.75 13.4142 12.4142 13.75 12 13.75H3.75V18C3.75 19.2426 4.75736 20.25 6 20.25H11C11.4142 20.25 11.75 20.5858 11.75 21C11.75 21.4142 11.4142 21.75 11 21.75H6C3.92893 21.75 2.25 20.0711 2.25 18V9.5C2.25 7.70507 3.70507 6.25 5.5 6.25H7.75V5C7.75 3.48122 8.98122 2.25 10.5 2.25H13.5ZM10.5 3.75C9.80964 3.75 9.25 4.30964 9.25 5V6.25H14.75V5C14.75 4.30964 14.1904 3.75 13.5 3.75H10.5Z" fill="black"/>
</svg>',

		'desc'    => 'Let candidates apply for jobs by submitting their details, resume, and cover letter.',
		'content' => '',
		'is_pro'  => true,
	),
	array(
		'id'      => 'support-request',
		'name'    => 'Support Request',
		
		'icon'    =>  '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path fill-rule="evenodd" clip-rule="evenodd" d="M7.18265 3.52372C10.166 1.82688 13.832 1.82625 16.8164 3.5247C17.4798 3.14913 18.3437 3.23688 18.918 3.80986L20.1914 5.08329L20.3194 5.22587C20.7775 5.79525 20.8214 6.57617 20.4746 7.18681C22.1719 10.1708 22.171 13.8341 20.4746 16.8167C20.8502 17.48 20.7642 18.343 20.1914 18.9173L18.918 20.1907C18.343 20.7657 17.4797 20.8504 16.8164 20.4749C13.8325 22.1718 10.1666 22.1726 7.18265 20.4729C6.51944 20.8486 5.65739 20.7636 5.08304 20.1907L3.80961 18.9173C3.23476 18.3424 3.14915 17.4789 3.52445 16.8157C1.82632 13.8315 1.82697 10.1661 3.52347 7.1829C3.14877 6.5196 3.23598 5.65634 3.80863 5.08232L5.08207 3.80888L5.22465 3.68095C5.79314 3.2234 6.57254 3.17885 7.18265 3.52372ZM14.1055 17.3684C12.7542 17.8976 11.2448 17.8979 9.89359 17.3684L8.26078 19.3518C10.6071 20.5474 13.3924 20.5477 15.7393 19.3538L14.1055 17.3684ZM16.6289 15.4397C16.4633 15.6621 16.28 15.8756 16.0782 16.0774C15.8762 16.2794 15.6619 16.4625 15.4395 16.6282L17.4834 19.1116L17.5225 19.1507C17.6207 19.228 17.7652 19.2224 17.8575 19.1302L19.1289 17.8577L19.1641 17.8147C19.2218 17.7241 19.2153 17.6063 19.1495 17.5227L19.1114 17.4837L16.6289 15.4397ZM4.88871 17.4827C4.77358 17.5776 4.76479 17.7513 4.87015 17.8567L6.14164 19.1282C6.24812 19.2344 6.4225 19.225 6.51664 19.1106L8.55961 16.6272C8.33766 16.4618 8.12443 16.279 7.92289 16.0774C7.72068 15.8752 7.53703 15.6615 7.37113 15.4388L4.88871 17.4827ZM14.8584 8.83134C13.1835 7.31797 10.5978 7.3687 8.98344 8.98271C7.31708 10.6491 7.31709 13.3505 8.98344 15.0169C10.6498 16.6832 13.3513 16.6832 15.0176 15.0169C16.6316 13.4026 16.6822 10.8168 15.169 9.14189L15.0176 8.98271L14.8584 8.83134ZM17.3692 9.89482C17.898 11.2458 17.8974 12.7548 17.3682 14.1058L19.3536 15.7395C20.5473 13.3933 20.5475 10.6087 19.3526 8.262L17.3692 9.89482ZM4.64554 8.25907C3.45128 10.6053 3.4517 13.3907 4.64652 15.7376L6.6309 14.1048C6.10205 12.7537 6.10146 11.2438 6.6309 9.89286L4.64554 8.25907ZM17.8584 4.87138C17.7653 4.77862 17.6212 4.7741 17.5235 4.85087L17.4844 4.88896L15.4395 7.37236C15.6566 7.53417 15.8658 7.71216 16.0635 7.90849L16.0782 7.92216L16.2832 8.137C16.4068 8.27374 16.5218 8.41567 16.6299 8.56083L19.1123 6.51786L19.1514 6.4788C19.2179 6.39463 19.223 6.27597 19.1651 6.18583L19.1309 6.14384L17.8584 4.87138ZM6.47758 4.84892C6.39336 4.78239 6.27478 4.77719 6.18461 4.83525L6.14261 4.86943L4.86918 6.14091L4.87015 6.14189C4.76411 6.24838 4.77339 6.42182 4.88773 6.51591L7.37113 8.55986C7.53683 8.33749 7.72099 8.12406 7.92289 7.92216C8.12477 7.72031 8.33825 7.53704 8.56058 7.37138L6.51664 4.88798L6.47758 4.84892ZM15.7393 4.64775C13.3921 3.45216 10.6065 3.45141 8.2598 4.64579L9.89359 6.63115C11.2446 6.10186 12.7544 6.10193 14.1055 6.63115L15.7393 4.64775Z" fill="black"/>
</svg>',

		'desc'    => 'Collect customer support inquiries or technical issues with structured ticket details.',
		'content' => '',
		'is_pro'  => true,
	),
	array(
		'id'       => 'volunteer-application',
		'name'     => 'Volunteer Application',
		'category' => 'nonprofit',
		'icon'     => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" stroke-width="1.5"/></svg>',
		'desc'     => 'Collect volunteer applications for your organization with a simple form.',
		'content'  => '',
		'is_pro'  => true,
	),
	array(
		'id'       => 'wholesale-inquiry',
		'name'     => 'Wholesale Inquiry',
		'category' => 'ecommerce',
		'icon'     => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7 18c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.58-6.49c.08-.14.12-.31.12-.48 0-.55-.45-1-1-1H5.21l-.94-2H1zm16 16c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2z" stroke-width="1.5"/></svg>',
		'desc'     => 'Collect wholesale and bulk order inquiries from businesses and resellers.',
		'content'  => '',
		'is_pro'  => true,
	),

);

return $templates;
