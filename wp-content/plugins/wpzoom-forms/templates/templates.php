<?php

$templates = array(
	array(
		'id'      => 'contact-form',
		'name'    => 'Contact Form',
		
		'icon'    => '<svg class="wpzoom-contact-form-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M3 9.703V19C3 20.105 3.895 21 5 21H18.999C20.104 21 20.999 20.105 20.999 19V9.703"  stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path fill-rule="evenodd" clip-rule="evenodd" d="M3.8863 8.04399L10.8803 3.33899C11.5573 2.88399 12.4423 2.88399 13.1193 3.33899L20.1133 8.04399C21.2913 8.83699 21.2913 10.57 20.1133 11.363L14.2393 15.315C12.8853 16.226 11.1153 16.226 9.7613 15.315L3.8873 11.363C2.7083 10.57 2.7083 8.83599 3.8863 8.04399Z"   stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M9.11984 14.88L3.58984 20.41"   stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M20.4099 20.41L14.8799 14.88"   stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M18 12.785V7.68698C18 6.58198 17.105 5.68698 16 5.68698H8C6.895 5.68698 6 6.58198 6 7.68698V12.785"   stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M9 9H15"   stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
<path d="M9.5 12H14.5"   stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
</svg>',

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
		
		'icon'    => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M9.14648 13.25C10.1355 13.2501 11.0879 13.6235 11.8135 14.2959C12.189 14.6434 12.4905 15.0596 12.7051 15.5215L12.792 15.7227L12.8164 15.7949C12.9208 16.1604 12.7337 16.5524 12.373 16.6963C12.0124 16.8401 11.6064 16.6846 11.4307 16.3477L11.3984 16.2773L11.2852 16.0332C11.1795 15.8351 11.0467 15.6528 10.8906 15.4912L10.7939 15.3965C10.3456 14.981 9.75727 14.7501 9.14648 14.75H7.85449C7.32007 14.75 6.80308 14.9266 6.38184 15.249L6.20703 15.3965C5.94247 15.6412 5.7354 15.9417 5.60156 16.2773C5.44821 16.662 5.01265 16.8495 4.62793 16.6963C4.24322 16.5429 4.05574 16.1074 4.20898 15.7227C4.42507 15.1806 4.75835 14.693 5.1875 14.2959L5.32617 14.1738C6.0323 13.5786 6.92708 13.25 7.85449 13.25H9.14648Z" />
<path d="M17.4766 12.2539C17.8548 12.2923 18.1504 12.6116 18.1504 13C18.1504 13.3884 17.8548 13.7077 17.4766 13.7461L17.4004 13.75H15C14.5858 13.75 14.25 13.4142 14.25 13C14.25 12.5858 14.5858 12.25 15 12.25H17.4004L17.4766 12.2539Z"  />
<path fill-rule="evenodd" clip-rule="evenodd" d="M6.37891 7.12891C7.55048 5.95733 9.44952 5.95733 10.6211 7.12891L10.7275 7.24023C11.7918 8.41788 11.7562 10.236 10.6211 11.3711C9.44952 12.5427 7.55048 12.5427 6.37891 11.3711C5.20733 10.1995 5.20733 8.30048 6.37891 7.12891ZM9.44629 8.08594C8.85714 7.60582 7.98848 7.64043 7.43945 8.18945C6.85367 8.77524 6.85367 9.72476 7.43945 10.3105C8.02524 10.8963 8.97476 10.8963 9.56055 10.3105C10.1096 9.76152 10.1442 8.89286 9.66406 8.30371L9.56055 8.18945L9.44629 8.08594Z"  />
<path d="M19 8.25C19.4142 8.25 19.75 8.58579 19.75 9C19.75 9.41421 19.4142 9.75 19 9.75H15C14.5858 9.75 14.25 9.41421 14.25 9C14.25 8.58579 14.5858 8.25 15 8.25H19Z"  />
<path fill-rule="evenodd" clip-rule="evenodd" d="M20 2.25C21.5192 2.25 22.75 3.48079 22.75 5V18C22.75 19.5192 21.5192 20.75 20 20.75H4C2.48079 20.75 1.25 19.5192 1.25 18V5.04102C1.25 3.4998 2.4998 2.25 4.04102 2.25H20ZM4.04102 3.75C3.32823 3.75 2.75 4.32823 2.75 5.04102V18C2.75 18.6908 3.30921 19.25 4 19.25H20C20.6908 19.25 21.25 18.6908 21.25 18V5C21.25 4.30921 20.6908 3.75 20 3.75H4.04102Z"  />
</svg>
',

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
<label for="input_name"><span>First</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><input type="text" name="input_name" id="input_name" placeholder="" required class="wp-block-wpzoom-forms-text-name-field"/>
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
<label for="input_3c1eb2cc"><span>State</span></label><select name="input_3c1eb2cc" id="input_3c1eb2cc" defaultvalue="Item #1" class="wp-block-wpzoom-forms-select-field"><option value="Alabama">Alabama</option><option value="Alaska">Alaska</option><option value="Arizona">Arizona</option><option value="Arkansas">Arkansas</option><option value="California">California</option><option value="Colorado">Colorado</option><option value="Connecticut">Connecticut</option><option value="Delaware">Delaware</option><option value="Florida">Florida</option><option value="Georgia">Georgia</option><option value="Hawaii">Hawaii</option><option value="Idaho">Idaho</option><option value="Illinois">Illinois</option><option value="Indiana">Indiana</option><option value="Iowa">Iowa</option><option value="Kansas">Kansas</option><option value="Kentucky">Kentucky</option><option value="Louisiana">Louisiana</option><option value="Maine">Maine</option><option value="Maryland">Maryland</option><option value="Massachusetts">Massachusetts</option><option value="Michigan">Michigan</option><option value="Minnesota">Minnesota</option><option value="Mississippi">Mississippi</option><option value="Missouri">Missouri</option><option value="Montana">Montana</option><option value="Nebraska">Nebraska</option><option value="Nevada">Nevada</option><option value="New Hampshire">New Hampshire</option><option value="New Jersey">New Jersey</option><option value="New Mexico">New Mexico</option><option value="New York">New York</option><option value="North Carolina">North Carolina</option><option value="North Dakota">North Dakota</option><option value="Ohio">Ohio</option><option value="Oklahoma">Oklahoma</option><option value="Oregon">Oregon</option><option value="Pennsylvania">Pennsylvania</option><option value="Rhode Island">Rhode Island</option><option value="South Carolina">South Carolina</option><option value="South Dakota">South Dakota</option><option value="Tennessee">Tennessee</option><option value="Texas">Texas</option><option value="Utah">Utah</option><option value="Vermont">Vermont</option><option value="Virginia">Virginia</option><option value="Washington">Washington</option><option value="West Virginia">West Virginia</option><option value="Wisconsin">Wisconsin</option><option value="Wyoming">Wyoming</option></select>
<!-- /wp:wpzoom-forms/select-field --></div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column"><!-- wp:wpzoom-forms/select-field {"id":"input_e6c773f3","name":"Country","options":["Afghanistan","Albania","Algeria","Andorra","Angola","Antigua \u0026 Deps","Argentina","Armenia","Australia","Austria","Azerbaijan","Bahamas","Bahrain","Bangladesh","Barbados","Belarus","Belgium","Belize","Benin","Bhutan","Bolivia","Bosnia Herzegovina","Botswana","Brazil","Brunei","Bulgaria","Burkina","Burundi","Cambodia","Cameroon","Canada","Cape Verde","Central African Rep","Chad","Chile","China","Colombia","Comoros","Congo","Congo (Democratic Rep)","Costa Rica","Croatia","Cuba","Cyprus","Czech Republic","Denmark","Djibouti","Dominica","Dominican Republic","East Timor","Ecuador","Egypt","El Salvador","Equatorial Guinea","Eritrea","Estonia","Ethiopia","Fiji","Finland","France","Gabon","Gambia","Georgia","Germany","Ghana","Greece","Grenada","Guatemala","Guinea","Guinea-Bissau","Guyana","Haiti","Honduras","Hungary","Iceland","India","Indonesia","Iran","Iraq","Ireland {Republic}","Israel","Italy","Ivory Coast","Jamaica","Japan","Jordan","Kazakhstan","Kenya","Kiribati","Korea North","Korea South","Kosovo","Kuwait","Kyrgyzstan","Laos","Latvia","Lebanon","Lesotho","Liberia","Libya","Liechtenstein","Lithuania","Luxembourg","Macedonia","Madagascar","Malawi","Malaysia","Maldives","Mali","Malta","Marshall Islands","Mauritania","Mauritius","Mexico","Micronesia","Moldova","Monaco","Mongolia","Montenegro","Morocco","Mozambique","Myanmar, {Burma}","Namibia","Nauru","Nepal","Netherlands","New Zealand","Nicaragua","Niger","Nigeria","Norway","Oman","Pakistan","Palau","Panama","Papua New Guinea","Paraguay","Peru","Philippines","Poland","Portugal","Qatar","Romania","Russian Federation","Rwanda","St Kitts \u0026 Nevis","St Lucia","Saint Vincent \u0026 the Grenadines","Samoa","San Marino","Sao Tome \u0026 Principe","Saudi Arabia","Senegal","Serbia","Seychelles","Sierra Leone","Singapore","Slovakia","Slovenia","Solomon Islands","Somalia","South Africa","South Sudan","Spain","Sri Lanka","Sudan","Suriname","Swaziland","Sweden","Switzerland","Syria","Taiwan","Tajikistan","Tanzania","Thailand","Togo","Tonga","Trinidad \u0026 Tobago","Tunisia","Turkey","Turkmenistan","Tuvalu","Uganda","Ukraine","United Arab Emirates","United Kingdom","United States","Uruguay","Uzbekistan","Vanuatu","Vatican City","Venezuela","Vietnam","Yemen","Zambia","Zimbabwe"],"label":"Country"} -->
<label for="input_e6c773f3"><span>Country</span></label><select name="input_e6c773f3" id="input_e6c773f3" defaultvalue="Item #1" class="wp-block-wpzoom-forms-select-field"><option value="Afghanistan">Afghanistan</option><option value="Albania">Albania</option><option value="Algeria">Algeria</option><option value="Andorra">Andorra</option><option value="Angola">Angola</option><option value="Antigua &amp; Deps">Antigua &amp; Deps</option><option value="Argentina">Argentina</option><option value="Armenia">Armenia</option><option value="Australia">Australia</option><option value="Austria">Austria</option><option value="Azerbaijan">Azerbaijan</option><option value="Bahamas">Bahamas</option><option value="Bahrain">Bahrain</option><option value="Bangladesh">Bangladesh</option><option value="Barbados">Barbados</option><option value="Belarus">Belarus</option><option value="Belgium">Belgium</option><option value="Belize">Belize</option><option value="Benin">Benin</option><option value="Bhutan">Bhutan</option><option value="Bolivia">Bolivia</option><option value="Bosnia Herzegovina">Bosnia Herzegovina</option><option value="Botswana">Botswana</option><option value="Brazil">Brazil</option><option value="Brunei">Brunei</option><option value="Bulgaria">Bulgaria</option><option value="Burkina">Burkina</option><option value="Burundi">Burundi</option><option value="Cambodia">Cambodia</option><option value="Cameroon">Cameroon</option><option value="Canada">Canada</option><option value="Cape Verde">Cape Verde</option><option value="Central African Rep">Central African Rep</option><option value="Chad">Chad</option><option value="Chile">Chile</option><option value="China">China</option><option value="Colombia">Colombia</option><option value="Comoros">Comoros</option><option value="Congo">Congo</option><option value="Congo (Democratic Rep)">Congo (Democratic Rep)</option><option value="Costa Rica">Costa Rica</option><option value="Croatia">Croatia</option><option value="Cuba">Cuba</option><option value="Cyprus">Cyprus</option><option value="Czech Republic">Czech Republic</option><option value="Denmark">Denmark</option><option value="Djibouti">Djibouti</option><option value="Dominica">Dominica</option><option value="Dominican Republic">Dominican Republic</option><option value="East Timor">East Timor</option><option value="Ecuador">Ecuador</option><option value="Egypt">Egypt</option><option value="El Salvador">El Salvador</option><option value="Equatorial Guinea">Equatorial Guinea</option><option value="Eritrea">Eritrea</option><option value="Estonia">Estonia</option><option value="Ethiopia">Ethiopia</option><option value="Fiji">Fiji</option><option value="Finland">Finland</option><option value="France">France</option><option value="Gabon">Gabon</option><option value="Gambia">Gambia</option><option value="Georgia">Georgia</option><option value="Germany">Germany</option><option value="Ghana">Ghana</option><option value="Greece">Greece</option><option value="Grenada">Grenada</option><option value="Guatemala">Guatemala</option><option value="Guinea">Guinea</option><option value="Guinea-Bissau">Guinea-Bissau</option><option value="Guyana">Guyana</option><option value="Haiti">Haiti</option><option value="Honduras">Honduras</option><option value="Hungary">Hungary</option><option value="Iceland">Iceland</option><option value="India">India</option><option value="Indonesia">Indonesia</option><option value="Iran">Iran</option><option value="Iraq">Iraq</option><option value="Ireland {Republic}">Ireland {Republic}</option><option value="Israel">Israel</option><option value="Italy">Italy</option><option value="Ivory Coast">Ivory Coast</option><option value="Jamaica">Jamaica</option><option value="Japan">Japan</option><option value="Jordan">Jordan</option><option value="Kazakhstan">Kazakhstan</option><option value="Kenya">Kenya</option><option value="Kiribati">Kiribati</option><option value="Korea North">Korea North</option><option value="Korea South">Korea South</option><option value="Kosovo">Kosovo</option><option value="Kuwait">Kuwait</option><option value="Kyrgyzstan">Kyrgyzstan</option><option value="Laos">Laos</option><option value="Latvia">Latvia</option><option value="Lebanon">Lebanon</option><option value="Lesotho">Lesotho</option><option value="Liberia">Liberia</option><option value="Libya">Libya</option><option value="Liechtenstein">Liechtenstein</option><option value="Lithuania">Lithuania</option><option value="Luxembourg">Luxembourg</option><option value="Macedonia">Macedonia</option><option value="Madagascar">Madagascar</option><option value="Malawi">Malawi</option><option value="Malaysia">Malaysia</option><option value="Maldives">Maldives</option><option value="Mali">Mali</option><option value="Malta">Malta</option><option value="Marshall Islands">Marshall Islands</option><option value="Mauritania">Mauritania</option><option value="Mauritius">Mauritius</option><option value="Mexico">Mexico</option><option value="Micronesia">Micronesia</option><option value="Moldova">Moldova</option><option value="Monaco">Monaco</option><option value="Mongolia">Mongolia</option><option value="Montenegro">Montenegro</option><option value="Morocco">Morocco</option><option value="Mozambique">Mozambique</option><option value="Myanmar, {Burma}">Myanmar, {Burma}</option><option value="Namibia">Namibia</option><option value="Nauru">Nauru</option><option value="Nepal">Nepal</option><option value="Netherlands">Netherlands</option><option value="New Zealand">New Zealand</option><option value="Nicaragua">Nicaragua</option><option value="Niger">Niger</option><option value="Nigeria">Nigeria</option><option value="Norway">Norway</option><option value="Oman">Oman</option><option value="Pakistan">Pakistan</option><option value="Palau">Palau</option><option value="Panama">Panama</option><option value="Papua New Guinea">Papua New Guinea</option><option value="Paraguay">Paraguay</option><option value="Peru">Peru</option><option value="Philippines">Philippines</option><option value="Poland">Poland</option><option value="Portugal">Portugal</option><option value="Qatar">Qatar</option><option value="Romania">Romania</option><option value="Russian Federation">Russian Federation</option><option value="Rwanda">Rwanda</option><option value="St Kitts &amp; Nevis">St Kitts &amp; Nevis</option><option value="St Lucia">St Lucia</option><option value="Saint Vincent &amp; the Grenadines">Saint Vincent &amp; the Grenadines</option><option value="Samoa">Samoa</option><option value="San Marino">San Marino</option><option value="Sao Tome &amp; Principe">Sao Tome &amp; Principe</option><option value="Saudi Arabia">Saudi Arabia</option><option value="Senegal">Senegal</option><option value="Serbia">Serbia</option><option value="Seychelles">Seychelles</option><option value="Sierra Leone">Sierra Leone</option><option value="Singapore">Singapore</option><option value="Slovakia">Slovakia</option><option value="Slovenia">Slovenia</option><option value="Solomon Islands">Solomon Islands</option><option value="Somalia">Somalia</option><option value="South Africa">South Africa</option><option value="South Sudan">South Sudan</option><option value="Spain">Spain</option><option value="Sri Lanka">Sri Lanka</option><option value="Sudan">Sudan</option><option value="Suriname">Suriname</option><option value="Swaziland">Swaziland</option><option value="Sweden">Sweden</option><option value="Switzerland">Switzerland</option><option value="Syria">Syria</option><option value="Taiwan">Taiwan</option><option value="Tajikistan">Tajikistan</option><option value="Tanzania">Tanzania</option><option value="Thailand">Thailand</option><option value="Togo">Togo</option><option value="Tonga">Tonga</option><option value="Trinidad &amp; Tobago">Trinidad &amp; Tobago</option><option value="Tunisia">Tunisia</option><option value="Turkey">Turkey</option><option value="Turkmenistan">Turkmenistan</option><option value="Tuvalu">Tuvalu</option><option value="Uganda">Uganda</option><option value="Ukraine">Ukraine</option><option value="United Arab Emirates">United Arab Emirates</option><option value="United Kingdom">United Kingdom</option><option value="United States">United States</option><option value="Uruguay">Uruguay</option><option value="Uzbekistan">Uzbekistan</option><option value="Vanuatu">Vanuatu</option><option value="Vatican City">Vatican City</option><option value="Venezuela">Venezuela</option><option value="Vietnam">Vietnam</option><option value="Yemen">Yemen</option><option value="Zambia">Zambia</option><option value="Zimbabwe">Zimbabwe</option></select>
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
		
		'icon'    => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path fill-rule="evenodd" clip-rule="evenodd" d="M18.1768 13.7627C18.8602 13.0784 19.9681 13.0795 20.6514 13.7627L22.2373 15.3486C22.8794 15.9893 22.9181 17.0031 22.3574 17.6904L22.2373 17.8232L18.8232 21.2373C18.4946 21.5658 18.0497 21.75 17.5859 21.75H15C14.5859 21.75 14.2503 21.414 14.25 21V18.4141C14.25 17.9504 14.4342 17.5054 14.7627 17.1768L18.1768 13.7627ZM19.5908 14.8232C19.5046 14.7371 19.3726 14.7271 19.2764 14.791L19.2383 14.8232L15.8232 18.2373C15.7761 18.2845 15.75 18.3481 15.75 18.4141V20.25H17.5859C17.652 20.25 17.7154 20.2239 17.7627 20.1768L21.1768 16.7627L21.209 16.7236C21.2734 16.6263 21.2621 16.4947 21.1777 16.4102L19.5908 14.8232Z"  />
<path d="M16 3.25C18.0712 3.25 19.75 4.92879 19.75 7V10C19.75 10.4142 19.4142 10.75 19 10.75C18.5858 10.75 18.25 10.4142 18.25 10V7C18.25 5.75721 17.2428 4.75 16 4.75H6C4.75721 4.75 3.75 5.75721 3.75 7V16C3.75 17.2428 4.75721 18.25 6 18.25H11C11.4142 18.25 11.75 18.5858 11.75 19C11.75 19.4142 11.4142 19.75 11 19.75H6C3.92879 19.75 2.25 18.0712 2.25 16V7C2.25 4.92879 3.92879 3.25 6 3.25H16Z"  />
<path d="M11 13.25C11.4142 13.25 11.75 13.5858 11.75 14C11.75 14.4142 11.4142 14.75 11 14.75H7C6.58579 14.75 6.25 14.4142 6.25 14C6.25 13.5858 6.58579 13.25 7 13.25H11Z"  />
<path d="M15 8.25C15.4142 8.25 15.75 8.58579 15.75 9C15.75 9.41421 15.4142 9.75 15 9.75H7C6.58579 9.75 6.25 9.41421 6.25 9C6.25 8.58579 6.58579 8.25 7 8.25H15Z"  />
</svg>
',

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
		
		'icon'    => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M13.8555 17.4102C14.0366 17.0669 14.4465 16.9222 14.7988 17.0645L14.8682 17.0967L15.0732 17.1924C15.2961 17.2829 15.573 17.3534 15.8975 17.3535C16.3086 17.3535 16.6394 17.24 16.8867 17.1152L16.957 17.084C17.3119 16.9481 17.7196 17.1007 17.8945 17.4473C18.081 17.817 17.9323 18.2685 17.5625 18.4551C17.1559 18.6602 16.5938 18.8535 15.8975 18.8535C15.258 18.8534 14.7286 18.6898 14.3311 18.5039L14.1689 18.4238L14.1025 18.3848C13.7862 18.1744 13.6746 17.7535 13.8555 17.4102Z"  />
<path d="M13.9697 13.4951C14.3839 13.4951 14.7197 13.8309 14.7197 14.2451V14.7959C14.7197 15.2101 14.3839 15.5459 13.9697 15.5459C13.5556 15.5458 13.2197 15.21 13.2197 14.7959V14.2451C13.2197 13.831 13.5556 13.4953 13.9697 13.4951Z"  />
<path d="M17.8281 13.4951C18.2423 13.4952 18.5781 13.8309 18.5781 14.2451V14.7959C18.5781 15.2101 18.2423 15.5458 17.8281 15.5459C17.4139 15.5459 17.0781 15.2101 17.0781 14.7959V14.2451C17.0781 13.8309 17.4139 13.4951 17.8281 13.4951Z"  />
<path d="M8.04883 9.21289C8.68844 9.21292 9.21766 9.37661 9.61523 9.5625L9.77734 9.64258L9.84375 9.68164C10.1604 9.892 10.2719 10.3127 10.0908 10.6562C9.90953 10.9993 9.49972 11.1443 9.14746 11.002L9.07812 10.9697L8.87305 10.874C8.65008 10.7835 8.37342 10.7129 8.04883 10.7129C7.74045 10.7129 7.47723 10.777 7.26074 10.8613L7.05957 10.9512C6.68988 11.1374 6.2383 10.9887 6.05176 10.6191C5.86532 10.2495 6.01429 9.79802 6.38379 9.61133L6.54395 9.53613C6.93328 9.36176 7.43951 9.21294 8.04883 9.21289Z"  />
<path d="M6.17188 5.69824C6.58609 5.69824 6.92188 6.03403 6.92188 6.44824V6.99902C6.9218 7.41318 6.58604 7.74902 6.17188 7.74902C5.75776 7.74896 5.42195 7.41313 5.42188 6.99902V6.44824C5.42188 6.03407 5.75772 5.69831 6.17188 5.69824Z"  />
<path d="M10.0303 5.69824C10.4444 5.69839 10.7803 6.03412 10.7803 6.44824V6.99902C10.7802 7.41308 10.4443 7.74888 10.0303 7.74902C9.6161 7.74902 9.28034 7.41318 9.28027 6.99902V6.44824C9.28027 6.03403 9.61606 5.69824 10.0303 5.69824Z"  />
<path fill-rule="evenodd" clip-rule="evenodd" d="M8.45312 1.25879C12.0734 1.44206 14.9519 4.43554 14.9512 8.10156L14.9443 8.40625C14.9333 8.64953 14.9073 8.88874 14.8711 9.12402C15.2062 9.07363 15.5493 9.04788 15.8984 9.04785C19.6825 9.04785 22.7497 12.1145 22.75 15.8984C22.75 19.6828 19.6815 22.75 15.8975 22.75C12.1135 22.7497 9.04688 19.6825 9.04688 15.8984C9.0469 15.5493 9.07264 15.2062 9.12305 14.8711C8.79023 14.9223 8.44909 14.9512 8.10059 14.9512C4.4357 14.951 1.44206 12.0736 1.25879 8.45312L1.25 8.10059C1.25022 4.3177 4.3177 1.25022 8.10059 1.25L8.45312 1.25879ZM15.8984 10.5479C15.2901 10.5479 14.706 10.6509 14.1611 10.8379C14.1137 10.8606 14.064 10.8782 14.0127 10.8906C12.5734 11.4327 11.4291 12.5762 10.8877 14.0156C10.8756 14.0647 10.8604 14.1127 10.8389 14.1582C10.6511 14.704 10.5469 15.2889 10.5469 15.8984C10.5469 18.854 12.9419 21.2497 15.8975 21.25C18.8534 21.25 21.25 18.8541 21.25 15.8984C21.2497 12.9429 18.854 10.5479 15.8984 10.5479ZM8.10059 2.75C5.14613 2.75022 2.75022 5.14613 2.75 8.10059L2.75684 8.37598C2.89996 11.2037 5.2381 13.451 8.10059 13.4512C8.61362 13.4512 9.11026 13.3734 9.58398 13.2354C10.2778 11.5928 11.5934 10.2762 13.2363 9.58301C13.3744 9.10996 13.4512 8.61456 13.4512 8.10156L13.4443 7.82617C13.3061 5.08716 11.1085 2.89156 8.36816 2.75684L8.10059 2.75Z"  />
</svg>
',

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
<label for="input_8e4aa5c6"><span>Which department do you have a suggestion for?</span><sup class="wp-block-wpzoom-forms-required">*</sup></label><select name="input_8e4aa5c6" id="input_8e4aa5c6" required defaultvalue="Item #1" class="wp-block-wpzoom-forms-select-field"><option value="Sales">Sales</option><option value="Customer Support">Customer Support</option><option value="Marketing">Marketing</option><option value="Development">Development</option><option value="Other">Other</option></select>
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