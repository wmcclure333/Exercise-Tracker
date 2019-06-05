<?php
	require('php/utility/AA_conf.php');
	require('php/dataHolders/dataLog.php');
	$data = new DataLog(1, 1);	//holds all daily totals for account id '1'
			
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<META name="keywords" content="">
<META name="description" content="">
<title>TapTapJump 2.0</title>
<link rel="icon" href="favicon.ico?" type="image/x-icon" />

<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
<meta name="apple-mobile-web-app-capable" content="yes"/>
<meta name="apple-mobile-web-app-status-bar-style" content="black"/>
<link type="text/css" rel="apple-touch-icon-precomposed" href="images/iphone-icon.png?v=1"/>
<!--<link type="text/css" rel="apple-touch-startup-image" href="assets/images/template/startup_landscape.jpg?v=1" media="screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:landscape)"/>
<link type="text/css" rel="apple-touch-startup-image" href="assets/images/template/startup_portrait.jpg?v=1" media="screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:portrait)"/>
-->
<link rel="stylesheet" href="css/styles.css?v=33" />
<link rel="stylesheet" href="css/type/stylesheet.css?v=1" />
<link href="css/jquery.mCustomScrollbar.css" rel="stylesheet" type="text/css" />
  
<script src="js/jquery/jquery-1.6.min.js" type="text/javascript" charset="utf-8"></script>
<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<script src="js/jquery/jquery.ui.touch-punch.min.js"></script>
<script src="js/anim/TweenMax.min.js" type="text/javascript" charset="utf-8"></script>
<script src="js/jquery/jquery.mCustomScrollbar.concat.min.js"></script>   	
<script src="js/Workout.js?v=1" type="text/javascript" charset="utf-8"></script>
<script src="js/WeightTrainingWorkout.js?v=1" type="text/javascript" charset="utf-8"></script>
<script src="js/AppInterface.js?v=1" type="text/javascript" charset="utf-8"></script>
<script src="js/MainPanelGrid.js?v=1" type="text/javascript" charset="utf-8"></script>
<script src="js/PopupWindow.js?v=1" type="text/javascript" charset="utf-8"></script>
<script src="js/PopupQuickAlert.js?v=1" type="text/javascript" charset="utf-8"></script>
<script src="js/GridTwoColumnLayout.js?v=1" type="text/javascript" charset="utf-8"></script>
<script src="js/GridSplitListLayout.js?v=1" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" charset="utf-8">
	var theScore = 0;
	
	$(document).ready(function(e) {	//start the program
		var appInterface = new AppInterface();
	});
</script>
<script type="text/javascript" src="//use.typekit.net/kty8uzu.js"></script>
<script type="text/javascript">try{Typekit.load();}catch(e){}</script>
</head>

<body userId="<?php echo $data->user_id; ?>">
	<div id="ctr">
    	<section id="ALERTS"><!-- Alerts will be dynamically created in this section as needed.  See PopupQuickAlert.js. -->
		</section>
    	<section id="POPUPS">
         	<div id="show_history" class="list_popup"><!-- will be dynamically filled with **show_workout_history.html** --></div>
       		<div id="add_workout_type_popup" class="popup"><!-- will be dynamically filled with **add_workout_type_popup.html** --></div>
       		<div id="workout_entry_popup" class="popup"><!-- will be dynamically filled with AHR/minutes form **workout_log_form.html** --></div>
        </section>
        <div id="logo_ctr"><img src="images/ttj_logo.png" width="185" height="50"  alt=""/></div>
    	<div id="status_bar">
        	<div id="status_bar_top"><img src="images/status_bar_top.png" width="1152" height="19"  alt=""/></div>
            <div id="status_bar_left_column">
                <div id="score_meter">
                	<div id="score_title">Score</div>
                    <div id="score_txt">
                        <span id="score_text_number"><?php echo $data->current_score; ?></span><span id='score_txt_pts'>PT</span>
                    </div>
                    <span id="score_meter_overlay">
                        <img src="images/scoreMeter.png" width="305" height="55">
                    </span>
                    <span id="score_meter_bar"></span>
                </div>
            </div>
            <div id="status_bar_middle_column">
            	<div id="level_badge">Walt is at Level <span id="level_badge_value"><?php echo $data->current_level; ?></span></div>
            	<div id="status_most_recent">Your last workout was on <span id="last_workout_date"><?php echo date("m/d/Y", $data->a_last_workout['date']); ?></span> (<span id="last_workout_type"><?php echo $data->a_last_workout['type']; ?></span>)</div>
                <div id="status_bar_middle_column_left_section">
                	<div class="middle_column_subheader">HR Max</div>
                    <div id="hr_avg_copy">~<?php echo $data->current_max_hr; ?></div>
                </div>
                <div id="status_bar_middle_column_right_section">
                    <div class="middle_column_subheader">This week</div>
                    <div id="weekly_stats_copy"><span id="total_weekly_mins"><?php echo $data->total_weekly_minutes; ?></span> mins &nbsp;|&nbsp; <span id="total_weekly_exercises"><?php echo $data->total_weekly_exercises; ?></span> <?php if($data->total_weekly_exercises == 1) echo " exercise"; else echo " exercises"; ?> <br><span id="weekly_stats_day_copy">DAY <?php echo $data->current_day_in_week; ?> / 7</span></div>
                </div>
           </div>
            <div id="status_bar_right_column">
            	<div id="status_bar_right_column_title">My Progress</div>
                <div id="progress_btns_ctr">
                    <div id="progress_numbers_btn"><img src="images/progress_numbers_btn.png" width="88" height="68"  alt=""/></div>
                    <div id="progress_charts_btn"><img src="images/progress_charts_btn.png" width="88" height="68"  alt=""/></div>
                </div>
            </div>
            <div class="clear_float"></div>
        </div>	<!-- END #status_bar -->
                
      	<div id="main_workout_panels">
        	<div id="panel_row1" class="panel_row">
            	<!-- will be dynamically filled with exercise types **mainPanelData.php--getExerciseTypes() ** -->
            </div>
           	<!--<div class="pop_btn_left add_btn" id="exercise_type_add_btn"></div>   EVENTUALLY MOVE THIS INSIDE 'MY ACCOUNT' SECTION -->
       	</div><!-- END #main_workout_panels -->
        
        <div id="alerts_btn"><img src="images/alerts_btn.png" width="180" height="175"  alt=""/></div>
                     
        <div id="two_column_grid">
        	<div id="add_set_type_popup"></div>
			<div id="left_column">
            	<ul>
                	<!-- will be dynamically filled with li elements **getTwoColumnListData.php** -->
                </ul>
            </div>
   			<div id="right_column">
            	<ul class="sortable">
                </ul>
            </div>
            <div class="pop_btn_left add_btn" id="body_group_add_btn"></div>
	        <div class="pop_btn_right continue_btn" id="body_group_continue_btn"></div>
            <div class="pop_btn_right close_btn1" id="body_group_close_btn"></div>
            <div class="clear_float">
            </div>
		</div><!-- END #two_column_grid -->

        <div id="two_split_grid">
        	<div id="edit_set_popup"></div>
			<div id="left_box">
            	<ul><!-- will be dynamically filled with li elements **getTwoSplitListData.php** -->
                </ul>
            </div>
   			<div id="right_box">
                <!-- will be dynamically filled with set log form **weight_set_log_form.html** -->
            </div><!-- END #right_box -->
           <div class="pop_btn_right submit_btn1" id="submit_weight_workout_btn"></div>
           <div class="pop_btn_right close_btn2" id="close_weight_workout_btn"></div>
           <div class="clear_float">
            </div>
		</div><!-- END #two_split_grid -->

       	<div id="full_screen_underlayer">&nbsp;</div>
	</div><!-- END #ctr -->
    
    <!-- BEGIN AUDIO FILES -->
	<audio id="au_click_add">
        <source src="audio/click_add.mp3"></source>
        Your browser doesn't support audio.
    </audio>
	<audio id="au_click_delete">
        <source src="audio/click_delete.mp3"></source>
        Your browser doesn't support audio.
    </audio>
    <!-- END AUDIO FILES -->
</body>
</html>