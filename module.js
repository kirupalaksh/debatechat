// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
/* eslint camelcase: off */

M.mod_debatechat = M.mod_debatechat || {};
//var topic_old = $("#id_title").val();
	
$(document).ready(function() {
	if($("#id_grademethod").val() == 1) {
		$("#fitem_id_grade").show();
		}else {
        $("#fitem_id_grade").hide();
		}

        $("#id_grademethod").change(function() {
            if ($(this).val() === "1") {
                $("#fitem_id_grade").show();
				//$("#id_grademethod").val('Simple Direct Grading');  
            } else {
                $("#fitem_id_grade").hide();
            }
        });
    });
	
 $("#id_title").change(function() {
	 //$("#id_prompt").value = ' ';
     event.preventDefault(); // Prevent the default Enter key behavior (e.g., form submission) 
	const urlParams = new URLSearchParams(window.location.search);
	const course_val = urlParams.get('course');
	var topic = ($("#id_title").val()); console.log(topic);
	if(course_val != null && (topic)) {
	var coursefullname; 
	 $.ajax({
			  type: 'GET',             
			  url: 'https://stage2.lms.svhs.co/mod/debatechat/getcourse.php?id='+course_val,
			  success: function(result) { 
			  coursefullname = JSON.parse(result);
		
		//coursefullname = "Algebra 1, Part 1"
		let p1 = "I am a high school ";
	  let p2 = p1.concat(coursefullname);
	  let p3 = p2.concat(' student. You are to debate about ');
	  let p31 = p3.concat(topic);
	  let p4 = p31.concat(" with me, taking the opposite position to my own and presenting arguments.This is a high school educational activity. Please do not debate topics that are unsuitable for a high school teacher and student to discuss.Help me to learn about the topic through the discussion. Feel free to add tips and guidance to the student, as would a high school ");
	  let c1 = " teacher.For each return arguments to the student don't exceed 40 words.You should conclude the debate once I have argued atleast two most important crucial valid points about both sides of the debate.The response should be a valid json object format, the object contains 2 key value pairs,The both key value pairs should be enclosed by double quotes, argument: This should be your argument, concluded: This should be 'False' till conclude.If I argue unrelated to debate, simply respond with the same object, argument :'Let us stay focused on our topic.', concluded: 'False'.";  
	  let p5 = p4.concat(coursefullname);
	  let actual_prompt = p5.concat(c1); 
	  $("#id_prompt").val(actual_prompt);  
		  
	  }
	
	
	});
	
	}
 
 			
 });






    


