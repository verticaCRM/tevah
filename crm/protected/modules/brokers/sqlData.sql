/*&*/ALTER TABLE x2_brokers ADD COLUMN c_address VARCHAR(255);/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_address', 'Address', '1', '1', 'varchar', '');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_comment VARCHAR(255);/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_comment', 'Comment', '1', '1', 'varchar', '');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_company VARCHAR(255);/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_company', 'Company', '1', '1', 'varchar', '');/*&*/INSERT INTO x2_dropdowns (name, options, multi, parent, parentVal) VALUES ('Countries', '{"US":"US","Canada":"Canada","England":"England","Australia":"Australia","Israel":"Israel"}', '0', '', '');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_country VARCHAR(255);/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_country', 'Country', '1', '1', 'dropdown', 'Countries');/*&*/INSERT INTO x2_dropdowns (name, options, multi, parent, parentVal) VALUES ('Education', '{"High School":"High School","Some College":"Some College","College Graduate":"College Graduate","Masters Degree":"Masters Degree","Professional Degree":"Professional Degree"}', '0', '', '');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_education VARCHAR(255);/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_education', 'Education', '1', '1', 'dropdown', 'Education');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_email VARCHAR(255);/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_email', 'Email Address', '1', '1', 'email', '');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_emailaddres VARCHAR(255);/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_emailaddres', 'Email', '1', '1', 'email', '');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_facebookaddress VARCHAR(255);/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_facebookaddress', 'Facebook Address', '1', '1', 'varchar', '');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_fax VARCHAR(40);/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_fax', 'Fax', '1', '1', 'phone', '');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_fee BIGINT;/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_fee', 'Fee:', '1', '1', 'int', '');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_firstName VARCHAR(255);/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_firstName', 'First Name', '1', '1', 'varchar', '');/*&*/INSERT INTO x2_dropdowns (name, options, multi, parent, parentVal) VALUES ('Gender', '{"Male":"Male","Female":"Female"}', '0', '', '');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_gender VARCHAR(255);/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_gender', 'Gender', '1', '1', 'dropdown', 'Gender');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_hiredate BIGINT;/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_hiredate', 'Hire Date:', '1', '1', 'date', '');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_lastName VARCHAR(255);/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_lastName', 'Last Name', '1', '1', 'varchar', '');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_linkedinaddress VARCHAR(255);/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_linkedinaddress', 'LinkedIN Address', '1', '1', 'varchar', '');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_linkedinpage VARCHAR(255);/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_linkedinpage', 'LinkedIn Address', '1', '1', 'varchar', '');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_misc10 DECIMAL(18,2);/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_misc10', 'Misc 5:', '1', '1', 'currency', '');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_misc7 DECIMAL(18,2);/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_misc7', 'Misc 2:', '1', '1', 'currency', '');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_mobile VARCHAR(40);/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_mobile', 'Cell Phone', '1', '1', 'phone', '');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_office VARCHAR(40);/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_office', 'Office Phone', '1', '1', 'phone', '');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_paidon BIGINT;/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_paidon', 'Paid On:', '1', '1', 'date', '');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_paymentnotes TEXT;/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_paymentnotes', 'Payment Notes', '1', '1', 'text', '');/*&*/INSERT INTO x2_dropdowns (name, options, multi, parent, parentVal) VALUES ('Payment Status', '{"Paid":"Paid","Not Paid":"Not Paid","Waived":"Waived"}', '0', '', '');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_paymentstatus VARCHAR(255);/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_paymentstatus', 'Payment Status', '1', '1', 'dropdown', 'Payment Status');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_position VARCHAR(255);/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_position', 'Position', '1', '1', 'varchar', '');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_profilePicture VARCHAR(255);/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_profilePicture', 'Profile Picture', '1', '1', 'link', 'Media');/*&*/INSERT INTO x2_dropdowns (name, options, multi, parent, parentVal) VALUES ('Proof of Funds', '{"Yes":"Yes","No":"No"}', '0', '', '');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_Proofoffunds VARCHAR(255);/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_Proofoffunds', 'Proof of Funds', '1', '1', 'dropdown', 'Proof of Funds');/*&*/INSERT INTO x2_dropdowns (name, options, multi, parent, parentVal) VALUES ('US States', '{"Alabama":"Alabama","Alaska":"Alaska","Arizona":"Arizona","Arkansas":"Arkansas","California":"California","Colorado":"Colorado","Connecticut":"Connecticut","Delaware":"Delaware","District of Columbia":"District of Columbia","Florida":"Florida","Georgia":"Georgia","Hawaii":"Hawaii","Idaho":"Idaho","Illinois":"Illinois","Indiana":"Indiana","Iowa":"Iowa","Kansas":"Kansas","Kentucky":"Kentucky","Louisiana":"Louisiana","Maine":"Maine","Maryland":"Maryland","Massachusetts":"Massachusetts","Michigan":"Michigan","Minnesota":"Minnesota","Mississippi":"Mississippi","Missouri":"Missouri","Montana":"Montana","Nebraska":"Nebraska","Nevada":"Nevada","New Hampshire":"New Hampshire","New Jersey":"New Jersey","New Mexico":"New Mexico","New York":"New York","North Carolina":"North Carolina","North Dakota":"North Dakota","Ohio":"Ohio","Oklahoma":"Oklahoma","Oregon":"Oregon","Pennsylvania":"Pennsylvania","Rhode Island":"Rhode Island","South Carolina":"South Carolina","South Dakota":"South Dakota","Tennessee":"Tennessee","Texas":"Texas","Utah":"Utah","Vermont":"Vermont","Virginia":"Virginia","Washington":"Washington","West Virginia":"West Virginia","Wisconsin":"Wisconsin","Wyoming":"Wyoming"}', '0', '1001', 'US');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_region VARCHAR(255);/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_region', 'Region 1', '1', '1', 'dropdown', 'US States');/*&*/INSERT INTO x2_dropdowns (name, options, multi, parent, parentVal) VALUES ('US States', '{"Alabama":"Alabama","Alaska":"Alaska","Arizona":"Arizona","Arkansas":"Arkansas","California":"California","Colorado":"Colorado","Connecticut":"Connecticut","Delaware":"Delaware","District of Columbia":"District of Columbia","Florida":"Florida","Georgia":"Georgia","Hawaii":"Hawaii","Idaho":"Idaho","Illinois":"Illinois","Indiana":"Indiana","Iowa":"Iowa","Kansas":"Kansas","Kentucky":"Kentucky","Louisiana":"Louisiana","Maine":"Maine","Maryland":"Maryland","Massachusetts":"Massachusetts","Michigan":"Michigan","Minnesota":"Minnesota","Mississippi":"Mississippi","Missouri":"Missouri","Montana":"Montana","Nebraska":"Nebraska","Nevada":"Nevada","New Hampshire":"New Hampshire","New Jersey":"New Jersey","New Mexico":"New Mexico","New York":"New York","North Carolina":"North Carolina","North Dakota":"North Dakota","Ohio":"Ohio","Oklahoma":"Oklahoma","Oregon":"Oregon","Pennsylvania":"Pennsylvania","Rhode Island":"Rhode Island","South Carolina":"South Carolina","South Dakota":"South Dakota","Tennessee":"Tennessee","Texas":"Texas","Utah":"Utah","Vermont":"Vermont","Virginia":"Virginia","Washington":"Washington","West Virginia":"West Virginia","Wisconsin":"Wisconsin","Wyoming":"Wyoming"}', '0', '1001', 'US');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_region2 VARCHAR(255);/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_region2', 'Region 2', '1', '1', 'dropdown', 'US States');/*&*/INSERT INTO x2_dropdowns (name, options, multi, parent, parentVal) VALUES ('US States', '{"Alabama":"Alabama","Alaska":"Alaska","Arizona":"Arizona","Arkansas":"Arkansas","California":"California","Colorado":"Colorado","Connecticut":"Connecticut","Delaware":"Delaware","District of Columbia":"District of Columbia","Florida":"Florida","Georgia":"Georgia","Hawaii":"Hawaii","Idaho":"Idaho","Illinois":"Illinois","Indiana":"Indiana","Iowa":"Iowa","Kansas":"Kansas","Kentucky":"Kentucky","Louisiana":"Louisiana","Maine":"Maine","Maryland":"Maryland","Massachusetts":"Massachusetts","Michigan":"Michigan","Minnesota":"Minnesota","Mississippi":"Mississippi","Missouri":"Missouri","Montana":"Montana","Nebraska":"Nebraska","Nevada":"Nevada","New Hampshire":"New Hampshire","New Jersey":"New Jersey","New Mexico":"New Mexico","New York":"New York","North Carolina":"North Carolina","North Dakota":"North Dakota","Ohio":"Ohio","Oklahoma":"Oklahoma","Oregon":"Oregon","Pennsylvania":"Pennsylvania","Rhode Island":"Rhode Island","South Carolina":"South Carolina","South Dakota":"South Dakota","Tennessee":"Tennessee","Texas":"Texas","Utah":"Utah","Vermont":"Vermont","Virginia":"Virginia","Washington":"Washington","West Virginia":"West Virginia","Wisconsin":"Wisconsin","Wyoming":"Wyoming"}', '0', '1001', 'US');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_region3 VARCHAR(255);/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_region3', 'Region 3', '1', '1', 'dropdown', 'US States');/*&*/INSERT INTO x2_dropdowns (name, options, multi, parent, parentVal) VALUES ('US States', '{"Alabama":"Alabama","Alaska":"Alaska","Arizona":"Arizona","Arkansas":"Arkansas","California":"California","Colorado":"Colorado","Connecticut":"Connecticut","Delaware":"Delaware","District of Columbia":"District of Columbia","Florida":"Florida","Georgia":"Georgia","Hawaii":"Hawaii","Idaho":"Idaho","Illinois":"Illinois","Indiana":"Indiana","Iowa":"Iowa","Kansas":"Kansas","Kentucky":"Kentucky","Louisiana":"Louisiana","Maine":"Maine","Maryland":"Maryland","Massachusetts":"Massachusetts","Michigan":"Michigan","Minnesota":"Minnesota","Mississippi":"Mississippi","Missouri":"Missouri","Montana":"Montana","Nebraska":"Nebraska","Nevada":"Nevada","New Hampshire":"New Hampshire","New Jersey":"New Jersey","New Mexico":"New Mexico","New York":"New York","North Carolina":"North Carolina","North Dakota":"North Dakota","Ohio":"Ohio","Oklahoma":"Oklahoma","Oregon":"Oregon","Pennsylvania":"Pennsylvania","Rhode Island":"Rhode Island","South Carolina":"South Carolina","South Dakota":"South Dakota","Tennessee":"Tennessee","Texas":"Texas","Utah":"Utah","Vermont":"Vermont","Virginia":"Virginia","Washington":"Washington","West Virginia":"West Virginia","Wisconsin":"Wisconsin","Wyoming":"Wyoming"}', '0', '1001', 'US');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_region4 VARCHAR(255);/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_region4', 'Region 4', '1', '1', 'dropdown', 'US States');/*&*/INSERT INTO x2_dropdowns (name, options, multi, parent, parentVal) VALUES ('US States', '{"Alabama":"Alabama","Alaska":"Alaska","Arizona":"Arizona","Arkansas":"Arkansas","California":"California","Colorado":"Colorado","Connecticut":"Connecticut","Delaware":"Delaware","District of Columbia":"District of Columbia","Florida":"Florida","Georgia":"Georgia","Hawaii":"Hawaii","Idaho":"Idaho","Illinois":"Illinois","Indiana":"Indiana","Iowa":"Iowa","Kansas":"Kansas","Kentucky":"Kentucky","Louisiana":"Louisiana","Maine":"Maine","Maryland":"Maryland","Massachusetts":"Massachusetts","Michigan":"Michigan","Minnesota":"Minnesota","Mississippi":"Mississippi","Missouri":"Missouri","Montana":"Montana","Nebraska":"Nebraska","Nevada":"Nevada","New Hampshire":"New Hampshire","New Jersey":"New Jersey","New Mexico":"New Mexico","New York":"New York","North Carolina":"North Carolina","North Dakota":"North Dakota","Ohio":"Ohio","Oklahoma":"Oklahoma","Oregon":"Oregon","Pennsylvania":"Pennsylvania","Rhode Island":"Rhode Island","South Carolina":"South Carolina","South Dakota":"South Dakota","Tennessee":"Tennessee","Texas":"Texas","Utah":"Utah","Vermont":"Vermont","Virginia":"Virginia","Washington":"Washington","West Virginia":"West Virginia","Wisconsin":"Wisconsin","Wyoming":"Wyoming"}', '0', '1001', 'US');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_region5 VARCHAR(255);/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_region5', 'Region 5', '1', '1', 'dropdown', 'US States');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_source VARCHAR(255);/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_source', 'Source', '1', '1', 'varchar', '');/*&*/INSERT INTO x2_dropdowns (name, options, multi, parent, parentVal) VALUES ('Product Status', '{"Active":"Active","Inactive":"Inactive","Pending":"Pending"}', '0', '', '');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_status VARCHAR(255);/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_status', 'Status', '1', '1', 'dropdown', 'Product Status');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_submitted BOOLEAN NOT NULL DEFAULT 0;/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_submitted', 'Submitted:', '1', '1', 'boolean', '');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_title VARCHAR(255);/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_title', 'Title:', '1', '1', 'varchar', '');/*&*/ALTER TABLE x2_brokers ADD COLUMN c_zip VARCHAR(255);/*&*/INSERT INTO x2_fields (modelName, fieldName, attributeLabel, modified, custom, type, linkType) VALUES ('brokers', 'c_zip', 'Zip', '1', '1', 'varchar', '');/*&*/INSERT INTO x2_form_layouts (model, version, scenario, layout, defaultView, defaultForm, createDate, lastUpdated) VALUES ('Brokers', 'Default', 'Default', '{"version":"1.2","sections":[{"collapsible":false,"title":"Brokers Info","rows":[{"cols":[{"width":266.88889,"items":[{"name":"formItem_name","labelType":"left","readOnly":"0","height":"22","width":"198","tabindex":"undefined"},{"name":"formItem_c_title","labelType":"left","readOnly":"0","height":"22","width":"42","tabindex":"undefined"},{"name":"formItem_c_firstName","labelType":"left","readOnly":"0","height":"22","width":"113","tabindex":"undefined"},{"name":"formItem_c_lastName","labelType":"left","readOnly":"0","height":"22","width":"113","tabindex":"undefined"},{"name":"formItem_c_position","labelType":"left","readOnly":"0","height":"22","width":"112","tabindex":"undefined"},{"name":"formItem_c_office","labelType":"left","readOnly":"0","height":"22","width":"98","tabindex":"undefined"},{"name":"formItem_c_mobile","labelType":"left","readOnly":"0","height":"22","width":"98","tabindex":"undefined"},{"name":"formItem_c_fax","labelType":"left","readOnly":"0","height":"22","width":"97","tabindex":"undefined"},{"name":"formItem_c_email","labelType":"left","readOnly":"0","height":"22","width":"152","tabindex":"undefined"},{"name":"formItem_c_address","labelType":"left","readOnly":"0","height":"22","width":"152","tabindex":"undefined"},{"name":"formItem_c_zip","labelType":"left","readOnly":"0","height":"22","width":"92","tabindex":"undefined"},{"name":"formItem_c_company","labelType":"left","readOnly":"0","height":"22","width":"152","tabindex":"undefined"},{"name":"formItem_c_source","labelType":"left","readOnly":"0","height":"22","width":"152","tabindex":"undefined"},{"name":"formItem_c_comment","labelType":"left","readOnly":"0","height":"22","width":"237","tabindex":"undefined"}]},{"width":152.88889,"items":[{"name":"formItem_c_country","labelType":"left","readOnly":"0","height":"18","width":"136","tabindex":"undefined"},{"name":"formItem_c_region","labelType":"left","readOnly":"0","height":"18","width":"136","tabindex":"undefined"},{"name":"formItem_c_region2","labelType":"left","readOnly":"0","height":"18","width":"138","tabindex":"undefined"},{"name":"formItem_c_region3","labelType":"left","readOnly":"0","height":"18","width":"138","tabindex":"undefined"},{"name":"formItem_c_region4","labelType":"left","readOnly":"0","height":"18","width":"138","tabindex":"undefined"},{"name":"formItem_c_region5","labelType":"left","readOnly":"0","height":"18","width":"138","tabindex":"undefined"}]},{"width":168,"items":[{"name":"formItem_c_submitted","labelType":"left","readOnly":"0","height":"22","width":"17","tabindex":"undefined"},{"name":"formItem_c_fee","labelType":"left","readOnly":"0","height":"22","width":"112","tabindex":"undefined"},{"name":"formItem_c_paidon","labelType":"left","readOnly":"0","height":"22","width":"112","tabindex":"undefined"},{"name":"formItem_c_paymentstatus","labelType":"left","readOnly":"0","height":"18","width":"104","tabindex":"undefined"},{"name":"formItem_c_paymentnotes","labelType":"left","readOnly":"0","height":"82","width":"173","tabindex":"undefined"}]}]}]},{"collapsible":false,"title":"","rows":[{"cols":[{"width":308.88889,"items":[{"name":"formItem_c_profilePicture","labelType":"left","readOnly":"0","height":"22","width":"133","tabindex":"undefined"},{"name":"formItem_c_education","labelType":"left","readOnly":"0","height":"18","width":"138","tabindex":"undefined"}]},{"width":280,"items":[{"name":"formItem_c_linkedinpage","labelType":"left","readOnly":"0","height":"22","width":"182","tabindex":"undefined"},{"name":"formItem_c_facebookaddress","labelType":"left","readOnly":"0","height":"22","width":"182","tabindex":"undefined"}]}]}]},{"collapsible":false,"title":"","rows":[{"cols":[{"width":590,"items":[{"name":"formItem_c_gender","labelType":"left","readOnly":"0","height":"18","width":"68","tabindex":"undefined"},{"name":"formItem_c_status","labelType":"left","readOnly":"0","height":"18","width":"73","tabindex":"undefined"},{"name":"formItem_assignedTo","labelType":"left","readOnly":"0","height":"18","width":"58","tabindex":"undefined"}]}]}]},{"collapsible":false,"title":"","rows":[{"cols":[{"width":590,"items":[{"name":"formItem_description","labelType":"left","readOnly":"0","height":"112","width":"483","tabindex":"undefined"}]}]}]},{"collapsible":false,"title":"","rows":[{"cols":[{"width":590,"items":[{"name":"formItem_createDate","labelType":"left","readOnly":"0","height":"22","width":"152","tabindex":"undefined"},{"name":"formItem_c_hiredate","labelType":"left","readOnly":"0","height":"22","width":"152","tabindex":"undefined"},{"name":"formItem_updatedBy","labelType":"left","readOnly":"0","height":"18","width":"178","tabindex":"undefined"},{"name":"formItem_lastUpdated","labelType":"left","readOnly":"0","height":"22","width":"182","tabindex":"undefined"}]}]}]}]}', '1', '1', '1429200418', '1429200418');