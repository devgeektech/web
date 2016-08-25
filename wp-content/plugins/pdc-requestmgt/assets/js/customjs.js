jQuery('.upload_file_to_text').on('change',function(){ 
			// jQuery('.register_controls input[type="submit"]').prop('disabled', true);
			// var path='<?php bloginfo("template_url")?>/images/loading.gif';
			// jQuery(".upload_file_to_text").after('<div class="progress-div"><progress id="progressBar" value="0" max="100"></progress> Checking video <br></div>');
            //select the form and submit
            var formData = new FormData();
            formData.append("file", this.files[0]);
            var request = new XMLHttpRequest();
        // request.upload.addEventListener("progress", progressFunction, false);  
            request.open("POST", "http://test.setlr.com/wp-content/themes/setlr/send_file.php");
            request.onload = function(oEvent) {
                if (request.status == 200) {
					jQuery('.progress-div').hide();
					jQuery('.register_controls input[type="submit"]').prop('disabled', false);
                    var obj = JSON.parse(request.response);
                    jQuery('.form_content_from_file').text(obj.result);
                }
            };
            request.send(formData);
        });