<?php
/**
 * Redirect the user to the appropriate submission related page.
 *
 * @package   mod_debatechat
 * @category  backup
 * @copyright 2023 kirupalakshmi kirutry@gmail.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$courseid = required_param('courseid', PARAM_INT); // Course.
$id = required_param('id', PARAM_INT); // Id.
$debatechat = required_param('debatechat', PARAM_INT); // Debatechat.
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
require_course_login($course);
$debate_details = $DB->get_record('debatechat', array('id' => $debatechat));

$imageurl = $CFG->wwwroot . '/mod/debatechat/images/Dot.png';
$context = context_module::instance($id);
require_capability('mod/debatechat:grade', $context);
$strname = get_string('modulenameplural', 'mod_debatechat');
$PAGE->set_url('/mod/debatechat/index.php', array('id' => $debatechat));
//$PAGE->navbar->add($strname);
$PAGE->set_title("$course->shortname: $strname");
//$PAGE->set_heading("$debate_details->name");
//$PAGE->set_pagelayout('incourse');
$PAGE->requires->css(new \moodle_url('https://cdn.datatables.net/v/bs4/dt-2.0.2/fc-5.0.0/fh-4.0.1/datatables.min.css')); 
$PAGE->requires->js(new \moodle_url('https://cdn.datatables.net/v/bs4/dt-2.0.2/fc-5.0.0/fh-4.0.1/datatables.min.js'),true);
echo $OUTPUT->header();
?>
<div id="topicname" style='display:None;'><?php echo $debate_details->title; ?></div>
<!-- Review Chat History -->

<div class="modal fade chat-history-modal" id="chatHistory" tabindex="-1" aria-labelledby="chatHistoryLabel" aria-hidden="true" data-modal="modalmyModal" >
    <div class="modal-dialog" >
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-white" id="chatHistoryLabel">Chat History</h5>
            <button type="button" class="btn btn-link p-0 close-btn" data-bs-dismiss="modal" id="close-modal" aria-label="Close"><img src="images/x-close.svg" alt="close"/></button>				
            </div>
            <div class="modal-body1">               
				<!--<div id="chathistory" style="overflow-x: hidden;overflow-y: scroll;"> </div>   -->
		  </div>
           
        </div>
    </div>
</div> 
<!-- <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
             <h4 class="modal-title" id="myModalLabel1"><span style="margin-left: 175px">Chat History</span></h4>
			 <button type="button" class="close" data-dismiss="modal" data-action="hide" aria-label="Close" id="close-modal">
                    <i class="fa fa-times-circle-o" aria-hidden="true"></i>
                </button>
            </div>
           <div class="modal-body1">               
				<div id="chathistory" style="overflow-x: hidden;overflow-y: scroll;"> </div>     
            </div>
        </div>
	</div>
</div>-->
<!-- AI Grading -->
<div class="modal fade grade-modal" id="gradeModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
              <div class="modal-title"  id="myModalLabel"><img src="<?php echo $imageurl; ?>" width="75px" ></div>
			<button type="button" class="close" data-dismiss="modal" data-action="hide" aria-label="Close" id="close-modal1">
                    <i class="fa fa-times-circle-o" aria-hidden="true"></i>
                </button>
            </div>
            <div class="modal-body">
				 <div class="d-flex justify-content-center flex-column">
					
				</div>				
			</div>
        </div>
	</div>
</div>
<?php 	
 function digitGrade_to_lettergrade($gradeValue) {
    if ($gradeValue >= 98) {
        return "A+";
    } else if ($gradeValue >= 90) {
        return "A";
    } else if ($gradeValue >= 88) {
        return "A-";
    } else if ($gradeValue >= 85) {
        return "B+";
    } else if ($gradeValue >= 81) {
        return "B";
    } else if ($gradeValue >= 78) {
        return "B-";
    } else if ($gradeValue >= 75) {
        return "C+";
    } else if ($gradeValue >= 71) {
        return "C";
    } else if ($gradeValue >= 68) {
        return "C-";
    } else if ($gradeValue >= 67) {
        return "D+";
    } else if ($gradeValue >= 65) {
        return "D";
    } else if ($gradeValue >= 60) {
        return "D-";
    } else {
        return "F";
    }
}
?>
<?php
$sql = "SELECT username, idnumber,
firstname, lastname, email,ue.userid,debatechat,um.concluded
FROM {user_enrolments} ue
LEFT JOIN {enrol} en ON ue.enrolid = en.id
JOIN {user} uu ON uu.id = ue.userid 
JOIN {debatechat_user_mapping} um ON um.userid = uu.id 
WHERE en.courseid = $courseid AND debatechat=$debatechat";
$records = $DB->get_records_sql($sql);
 //$resp = "<h1 style='margin-bottom:35px;' >".'List of Participants - '. $debate_details->title."</h1>";
 echo "<h1 style='margin-bottom:35px;' >".'List of Participants - '. $debate_details->title."</h1>";
 $resp  = '<table id="debatetable" class="table table-bordered table-striped" style="width:100%">';
 $resp .= '<thead>';
 $resp .= "<tr>";
 $resp .= "<th style='width:100px'>First Name</th><th style='width:100px'>Last Name</th><th style='width:100px;'>Submission</th><th style='width:100px;'>ChatHistory</th><th style='width:100px;'>Status</th><th style='width:100px'>AI Grade</th><th style='width: 100px;'>Final Grade</th>";
 $resp .= "</tr>"; 
 $resp .= '</thead>';
 $resp .= "<tbody>";
 foreach($records as $record){	
	$gradefinal = '-';
	$firstname = $record->firstname;
	$lastname = $record->lastname;
	$email = $record->email;
	$concludestatus = $record->concluded;
	if($concludestatus == 1) { $conclude = 'Submitted'; } else { $conclude = 'No Submission' ;}
	$userid = $record->userid;
	$values = $userid .'-' .$id;
	$review_chat = $userid .'-' .$id. '-'. $firstname;
	$grades = $DB->get_record('debatechat_grades', array('debatechat' => $debatechat,'userid'=>$userid)); 
	//if(!empty($grades) && ($grades->userid == $userid) && ($concludestatus == 1))  { 
	if(!empty($grades) && ($grades->userid == $userid))  { 
		$gradesdiv = '<div class="submissiongraded">Graded</div>'; 
		$values = $values .'-' . $grades->grade;
		$gradesbutton = '<a href="#" class="mymodel" data-user-id="'.$values.'" data-toggle="modal" data-target="#gradeModal" class="btn btn-primary"> Review Grade </a>'; 
		$gradesval = $grades->grade;  
		//$gradefinal = round($gradesval,2).' / 100.00';
		$gradefinal = digitGrade_to_lettergrade(round($gradesval,2)) . ' ('. number_format($gradesval, 2). ')' ;
		} else {  
		$gradesdiv = '<div class="nograded">Not Graded</div>';	
		if ($concludestatus == 1)	{
		$gradesbutton = '<a href="#" class="mymodel" data-user-id="'.$values.'" data-toggle="modal" data-target="#gradeModal" class="btn btn-primary"> Review Grade </a>';
		}else {			
		$gradesbutton = '-';
		}
		//$gradesbutton = '<button type="button" class="grade-modal" data-bs-toggle="modal" data-bs-target="#gradeModal">Review Grade</button>';
		}
	$resp .= "<tr>";
	$resp .= "<td>".'<a href="/user/profile.php?id='.$userid.'" target="_new" >'.$firstname."</a></td>";
	$resp .= "<td>".$lastname."</td>";		
	$resp .= "<td>".$conclude."</td>";
	$resp .= "<td><a href='#' class='grademodel' data-user-id=".$review_chat." data-bs-toggle='modal' data-target='#chatHistory' >Review</a>"."</td>";
	$resp .= "<td>".$gradesdiv."</td>";		
	//$resp .= '<td id="gradediv"><div class="btn btn-primary" onclick="onclickgrade('.$userid.');">Grade</div></td>';
	$resp .= "<td>".$gradesbutton."</td>";
	//$resp .= "<td>".'<a href="/mod/debatechat/gradeform.php?id='.$id.'&courseid='.$courseid.'&userid='.$userid.'" target="_new" class="btn btn-primary">Grade</a>'."</td>";	
	$resp .= '<td>'.$gradefinal .'</td>';
	$resp .= "</tr>";
	}
 $resp .= "</tbody></table>";

 echo $resp;  
?>
<script>
 
function acceptfunction(activity_id,userid){
	$("#SaveGrade").prop("disabled", true);
     $.ajax({
    type: 'GET',
    url: 'currentgrade.php',
    data: { activity_id:activity_id, student_id : userid },
    success: function(response) { console.log(response);
        // Request was successful
        if(response == 'success') { 
			location.reload();
		}
    },
		error: function(xhr, status, error) {
        // Request failed
        console.error('Request failed. Error: ' + error);
		}
	}); 
} 

   $(document).ready(function() {

	$('#debatetable').DataTable({
	});
	 
	   $("#close-modal").click(function() {
		  //$("#myModal .modal-body1").empty();	
		   $("#chatHistory").modal('hide');
	   });
	   var globalnewrubrics;
	   var keysobjects;

       $('.mymodel').click(function(e) {
            e.preventDefault();
            userId = $(this).data('user-id'); console.log(userId);
			const myArray = userId.split("-");
			let userid = myArray[0];
			let activity_id = myArray[1];
			let finalgrade = myArray[2];
		    let htmlContent = ''; console.log(userid);
			var datatosend = { "student_id": userid, "activity_id": activity_id };
			  getrubric(activity_id, function(receivedrubrics) {
    // Process the result here
				if (receivedrubrics !== null) { 
				//let parsed_data = json.loads(receivedrubrics)
					receivedrubrics.forEach(function(item) {
				// Retrieve the value of the 'criteria_key' field for each item
					if (keysobjects !== '') {
						keysobjects += ', '; // Add comma if it's not the first key
					}
					keysobjects += item.criteria_key;
			  
					});  
				}
				globalnewrubrics = receivedrubrics;
				});
            $.ajax({
                url: 'https://api.vf.svhs.co/api/debate-bot',  
				type: 'GET',
				data: datatosend, 
				contentType:'application/json',	  
				dataType: 'json',  
                success: function(result) {
				var keyconcepts = 	globalnewrubrics; console.log(keyconcepts);
				  // Populate modal content
				/* for (const category in keyconcepts) { 
				console.log(keyconcepts[category]);
				const levels = keyconcepts[category];
				$.each(levels.criteria_levels, function(ind, fun) {
				console.log(fun.criteria_key);
				
				});
				} */
                var modalContent = '<table>';
				htmlContent += '<h2 class="title mb-3">AI Recommended Grades on each Key Concepts</h2>';
               // $.each(result.data.grade, function(i, f) {
					//const aival = f.ai;					
					//const predicted_grade = aival.predicted_grade; 					 
					// if (i === result.data.length - 1) {
						 let predicted_grade = result.data.grade.final_score;
						 const current_grade = result.data.grade.estimated_grade;			
			if (current_grade!='null') {			
			let total_marks = 0;
			//for (const category in current_grade) { 
			for (const category in current_grade) { 
					const gradeInfo = current_grade[category];
						htmlContent += '<p class="level">'+gradeInfo.criteria_key+'</p>';
						htmlContent += '<p>'+gradeInfo.criteria_levels.description+'</p>';
						//htmlContent += '<p class="scoreval">Grade: '+gradeInfo.criteria_levels.grade+'<span style="float:right;">'+'SCORE: '+gradeInfo.criteria_levels.mark+'</span></p>';
						htmlContent += '<p class="level">'+'Mark: '+ Math.floor(gradeInfo.criteria_levels.mark)+'</p>';
						htmlContent += '<p class="reasondiv">AI Feedback : '+gradeInfo.criteria_levels.reason+'</p>';
						htmlContent +=  '<hr>'
						//total_marks +=  Math.floor(gradeInfo.criteria_levels.mark);
					//}
				//}
			} //console.log(total_marks);
			//total_marks = (total_marks/30)*100;
			//let aiscore = letterGrade_to_digitgrade(predicted_grade); 
			let aiscore = predicted_grade; 
			total_marks =  Math.floor(aiscore);
			
			htmlContent += '<p class="totalgrade">AI Recommended Grade: '+total_marks+' ('+digitGrade_to_lettergrade(total_marks)+')'+'</p>';
			// htmlContent += '<p class="totalgrade">AI Recommended Grade: '+digitGrade_to_lettergrade(total_marks)+' ('+total_marks+')'+'</p>';
			}
		// Update the content of the <div> with the constructed HTML
		//predictedGradeDiv.innerHTML = htmlContent;
		modalContent += '<tr><td>' + htmlContent + '</td></tr>';	
		//}
			
                //});
				modalContent += '<div class="text-end"><tr><td><p style="float:right">';
				if (finalgrade) { //console.log(finalgrade);				
				modalContent += '<button onclick="acceptfunction('+activity_id+','+userid+');" type="button" style="cursor: no-drop;background: #a2c4e4;" class="btn btn-primary" id="SaveGrade">Accept</button>';
				}else {
				modalContent += '<button onclick="acceptfunction('+activity_id+','+userid+');" type="button" class="btn btn-primary" id="SaveGrade">Accept</button>';	
				}
				 modalContent += '&nbsp;<a href="/mod/debatechat/gradeform.php?id='+activity_id+'&userid='+userid+'"  target="_blank" class="btn btn-primary" >Modify</a></td></tr></div>';
				// Add other data fields as needed
                modalContent += '</table>';

                // Display modal
                $('#gradeModal .modal-body').html(modalContent);
               
				$('#gradeModal').modal('show'); 
                },
                error: function(xhr, status, error) {
                    console.error('Error sending data:', error);
                }
            });
        });	
		
		
	
		
		 $('.grademodel').click(function(e) {			
            e.preventDefault();
            const userId = $(this).data('user-id'); 
            let topicname = $('#topicname').text(); 
			const myArray = userId.split("-");
			let userid = myArray[0];
			let activity_id = myArray[1];
			let studname = myArray[2];
			var messageElement = document.createElement("div");
			//$("#m-firstname").val(studname); 
			let coursename =  'Algebra 1, Part 1';
			// $('#myModal .modal-body').html(coursename);
			var getinput = { "student_id": userid, "activity_id": activity_id };
			$.ajax({
		  type: 'GET',   
		  url: 'https://api.vf.svhs.co/api/debate-bot',  
		  data: getinput, 
		  contentType:'application/json',	  
		  dataType: 'json',         // Expected data type of the response
		  success: function(result) {
			messageElement.innerHTML += '<h4>'+topicname+'</h4>';	
			if(result.message === undefined ) {
				var ulElement = document.createElement("ul");
				ulElement.className = "px-2 chat-history-list"; // Add class to 'ul' element	
				const airesponse = result.data.chat;
				const gradeval = result.data.grade; 
				$.each(airesponse, function(index, items) { 
				const studentinput = items.student;			
				const argumentval = items.ai;
				let predicted_score = gradeval.final_score;		  
		  var liUser = document.createElement("li");
		  if(studentinput != '') {
		  liUser.className = "d-flex gap-3 receiver-text";
		  liUser.innerHTML = '<div class="profile-circle-user"></div><p style="margin-left:20px;"><span>' + studname + '</span>' + studentinput + '</p>';
		  }
		  var liAI = document.createElement("li");
		  liAI.className = "d-flex gap-3 sender-text";
		  liAI.innerHTML = '<div class="profile-circle-dot"></div><p style="margin-left:20px;"><span>Dot</span>' + argumentval + '</p>';
		  // Append 'li' elements to 'ul'
		  ulElement.appendChild(liUser);
		  ulElement.appendChild(liAI);			
		});
		// Append 'ul' element to 'messageElement'
		messageElement.appendChild(ulElement);
			}else {
			messageElement.innerHTML += '<h5 style="text-align: center;align:center;">'+'No Chat History'+'</h5>';			
			}
		// Update modal content and show modal
		$('#chatHistory .modal-body1').html(messageElement);
		$('#chatHistory').modal('show');
	},  error: function(jqXHR, textStatus, errorThrown) {
			  // Handle errors here
			  console.error('AJAX Error:', textStatus, errorThrown);
		  }		
		});
			
			
		});	
		
		
			function appendMessage(sender, message,dispName) {
		let chathistory = document.getElementById("chathistory");
		var messageElement = document.createElement("div");
		//messageElement.classList.add("mt-2");
		 var stud_name = dispName; 
		if (sender == "user"){ 
		 messageElement.innerHTML = '<div class="chat-message-right pb-4"><div><img src="https://bootdey.com/img/Content/avatar/avatar3.png" class="rounded-circle mr-1" alt="user-profile" width="40" height="40"></div><div class="flex-shrink-1 bg-light rounded py-2 px-3 ml-3"><div class="font-weight-bold mb-1">'+stud_name+'</div><div class="user-msg">' + message + '</div></div></div>';
		} else if(sender == "bot") { 
		  messageElement.innerHTML = '<div class="chat-message-left pb-4"><div><img src="<?php echo $imageurl; ?>" class="rounded-circle mr-1" alt="debate bot" width="40" height="40"></div><div class="flex-shrink-1 rounded py-2 px-3 mr-3"><div class="font-weight-bold mb-1">Dot</div><div class="bot-msg">' + message + '</div></div></div>';
		}
	   chathistory.appendChild(messageElement);		
	}
		
	// get rubric function 
function getrubric(debateid,callback) { 
    $.ajax({
        type: 'POST',   
        url: 'getrubrics.php', 
        data: { debateid: debateid },
        success: function(result) {   
            var newrubrics = JSON.parse(result);
			//return newrubrics;
            // Call the callback function with the result
            callback(newrubrics); 	 
        },
        error: function(jqXHR, textStatus, errorThrown) {
            // Handle errors here
            //console.error('AJAX Error:', textStatus, errorThrown);
            // Call the callback function with null or an error indicator
            callback(null);
        }		
    });
}



function digitGrade_to_lettergrade(gradeValue) {
    if (gradeValue >= 98) {
        return "A+";
    } else if (gradeValue >= 90) {
        return "A";
    } else if (gradeValue >= 88) {
        return "A-";
    } else if (gradeValue >= 85) {
        return "B+";
    } else if (gradeValue >= 81) {
        return "B";
    } else if (gradeValue >= 78) {
        return "B-";
    } else if (gradeValue >= 75) {
        return "C+";
    } else if (gradeValue >= 71) {
        return "C";
    } else if (gradeValue >= 68) {
        return "C-";
    } else if (gradeValue >= 67) {
        return "D+";
    } else if (gradeValue >= 65) {
        return "D";
    } else if (gradeValue >= 60) {
        return "D-";
    } else {
        return "F";
    }
}



 function letterGrade_to_digitgrade(gradeValue){
	 var gradeDigit;
		if(gradeValue=="A"){
			gradeDigit = 97;
		}else if(gradeValue=="A+"){
			gradeDigit = 99;
		}else if(gradeValue=="A-"){
			gradeDigit = 90;
		}else if(gradeValue=="B+"){
			gradeDigit = 88;
		}else if(gradeValue=="B"){
			gradeDigit = 85;
		}else if(gradeValue=="B-"){
			gradeDigit = 81;
		}else if(gradeValue=="C+"){
			gradeDigit = 78;
		}else if(gradeValue=="C"){
			gradeDigit = 75;
		}else if(gradeValue=="C-"){
			gradeDigit = 71;
		}else if(gradeValue=="D+"){
			gradeDigit = 68;
		}else if(gradeValue=="D"){
			gradeDigit = 67;				  
		}else if(gradeValue=="D-"){
			gradeDigit = 65;
		}else if(gradeValue=="F"){
			gradeDigit = 60;
		} /*elseif(gradeValue=="P") {
			gradeDigit = 0.00;
		} elseif(gradeValue=="IP" ){
				gradeDigit = 0.00;
		}*/
		return gradeDigit;
	}
	
	
    });  

</script>

<style>

@import url('https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap') ;

html, body{
  font-family: Roboto;
}
div.submissiongraded {
    color: #000;
    background-color: #cfefcf;
}
div.nograded {
    color: #fff;
    background-color: #9e4c5d;
}
.hide{
  display:none;
}
.show{
  display:block;
}
.no-click {
    pointer-events: none;
}
     
.chat-message-left,
.chat-message-right {
    display: flex;
    flex-shrink: 0
}
.chat-messages {
    display: flex;
    flex-direction: column;
   /* max-height: 800px;
    overflow-y: scroll*/
}
.chat-message-left {
    margin-right: auto
}

.chat-message-right {
    flex-direction: row-reverse;
    margin-left: auto
}
.py-3 {
    padding-top: 1rem!important;
    padding-bottom: 1rem!important;
}
.px-4 {
    padding-right: 1.5rem!important;
    padding-left: 1.5rem!important;
}
.flex-grow-0 {
    flex-grow: 0!important;
}
.border-top {
    border-top: 1px solid #dee2e6!important;
}
.user-msg {
    float: right !important;
    text-align: left;
    color: #fff;
    animation: 300ms 1 forwards slideUpAndRight cubic-bezier(0.4, 0, 0.2, 1), 300ms 1 forwards fadeIn cubic-bezier(0.4, 0, 0.2, 1);
    background: #5594E8;
    border: none 0;
    border-radius: 10px;
    display: inline-block;
    margin-bottom: 1rem;
    outline: none;
    padding: 0.5rem 1rem;
    position: relative;
    word-wrap: break-word;
    max-width: 100%;
    box-shadow: 1px 1px 2px 0 #d9d9d9;
}
.bot-msg {
    text-align: left;
    color: #000;
    animation: 300ms 1 forwards slideUpAndRight cubic-bezier(0.4, 0, 0.2, 1), 300ms 1 forwards fadeIn cubic-bezier(0.4, 0, 0.2, 1);
    background: #E5EEFF;
    border: none 0;
    border-radius: 10px;
    display: inline-block;
    margin-bottom: 1rem;
    outline: none;
    padding: 0.5rem 1rem;
    position: relative;
    word-wrap: break-word;
    max-width: 100%;
    box-shadow: 1px 1px 2px 0 #d9d9d9;
}
.totalgrade {background-color: rgba(98, 151, 98, 1);color:#fff;font-size: 15px; padding: 5px 5px;text-align: -webkit-center;}
.level { font-family:Roboto;
font-weight:bold;}	

::-webkit-scrollbar {
    width: 5px;
    }

    /* Track */
    ::-webkit-scrollbar-track {
    background: transparent; 
    }
    
    /* Handle */
    ::-webkit-scrollbar-thumb {
    background:#a5a5a57d; 
    }

    /* Handle on hover */
    ::-webkit-scrollbar-thumb:hover {
    background: #a5a5a57d; 
    }
.grade-modal .modal-dialog{
      max-width: 600px;
    }
    .grade-modal .modal-content{
      border-radius: 12px;
    }
    .grade-modal .heading{
      font-family: Roboto;
      font-size: 14px;
      font-weight: 400;
      line-height: 19.1px;
      text-align: center;
      margin-bottom: 5px;
    }
    .grade-modal .content{
      font-family: Roboto;
      font-size: 14px;
      font-weight: 700;
      line-height: 19.1px;
      text-align: center;
      margin-bottom: 0;
    }
    .grade-modal .title{
      font-family: Roboto;
      font-size: 18px;
      font-weight: 700;
      line-height: 21.78px;
      text-align: center;
      text-transform: uppercase;
    }
    .grade-modal li{
      margin-bottom: 10px;
    }
    .grade-modal li:last-child{
      margin-bottom: 0;
    }
    .top-profile-circle{
      width: 150px;
      height: 150px;
      flex-shrink: 0;
      border-radius: 50%;
      background-color: #d1d1d1;
      background-image: url();
      background-position: center;
      background-repeat: no-repeat;
      background-size: cover;
      margin: auto;
      margin-bottom: 24px;
    }
    .grade-modal .modal-header{
      border: none;
      display: flex;
      justify-content: center;
      padding: 40px 40px 0;
    }
	.grade-modal .modal-header .close {
    position: absolute;
    background-color: transparent !important;
    color: #777 !important;
    font-size: 1.5rem;
    height: auto;
    line-height: 1;
    margin: 0px 0px 0px 501px;
    padding: 0px 5px 16px;
}
    .grade-modal .btn{
      font-size: 16px;     
      line-height: 24px;
      text-align: center;
      height: 40px;
      padding: 8px 24px;
      border-radius: 4px;
    }
    .btn-primary{
        color:#fff;
        background-color: #3165AE;
		font-size: 16px;
    font-family: Roboto;
    }
.modal-backdrop.fade {opacity: .5; }
.reasondiv {font-weight:bold;color:#2E65AE;}
.accepted {cursor: no-drop;background:#a2c4e4;}
.modal-dialog .modal-content {
border-radius: 12px; }
.chat-history-list .sender-text{
      margin: 20px 0;
    }
    .chat-history-list .sender-text p{
      background: #F2F2F2;
      padding: 20px;
      border-radius: 10px;
      font-family: Roboto;
      font-size: 15px;
      font-weight: 400;
      line-height: 21px;
      text-align: left;
      color: #000;
      max-width: 296px;
    }
    .chat-history-list {
    min-height: 530px;
    max-height: 530px;
    overflow-y: auto;
    }
    .chat-history-list .receiver-text{
      flex-direction: row-reverse;
      margin: 20px 0;
    }
    .chat-history-list .receiver-text p{
      background-color: #629CD1;
      font-family: Roboto;
      font-size: 15px;
      font-weight: 400;
      line-height: 21px;
      text-align: left;
      color: #fff;
      padding: 20px;
      border-radius: 10px;
      max-width: 296px;
    }
	.chat-history-modal .close-btn{
      width: 40px;
      height: 40px;
      background-color: #629CD1;
      border-radius: 100px;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .chat-history-modal .modal-header{
      background-color: #3165AE;
    }
    .chat-history-modal .modal-title{
      font-family: Roboto;
      font-size: 24px;
      font-weight: 600;
      line-height: 33.6px;
      text-align: left;
    }
    .chat-history-list .sender-text span,.chat-history-list .receiver-text span{
      display: block;
      font-weight: 700;
      margin-bottom: 0;
    }
    .chat-history-modal .modal-body1{
     border: 1px solid #DDDDDD;
     border-radius: 8px;
    }

	.chat-history-modal .profile-circle-dot{
		box-shadow: 0px 2.253520965576172px 2.253520965576172px 0px #00000040;
        width: 40px;
        height: 40px;
        background-color: #eee;
        background-image: url('https://stage2.lms.svhs.co/mod/historicalfigure/templates/Dot.png');
        background-repeat: no-repeat;
        background-position: center;
        background-size: cover;
        border-radius: 50%;
        flex-shrink: 0;
    }
	.chat-history-modal .profile-circle-user{
		box-shadow: 0px 2.253520965576172px 2.253520965576172px 0px #00000040;
        width: 40px;
        height: 40px;
        background-color: #eee;
        background-image: url('https://stage2.lms.svhs.co/mod/debatechat/images/avatar3.png');
        background-repeat: no-repeat;
        background-position: center;
        background-size: cover;
        border-radius: 50%;
        flex-shrink: 0;
    }
</style>
<?php  echo $OUTPUT->footer(); ?>


