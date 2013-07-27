<?php
require_once("../lib/init.php");
protectPage(8);

$q = "SELECT `ID` FROM `SurveyQuestions` WHERE `WardID`=$MEMBER->WardID ORDER BY `ID` ASC, `Visible` DESC";
$r = DB::Run($q);

?>
<html>
<head>
	<title>Manage Survey &mdash; <?php echo $WARD ? $WARD->Name." Ward" : SITE_NAME; ?></title>
	<?php include("../includes/head.php"); ?>
<style>
td {
	width: 25%;
	padding: 5px;
}

th {
	padding: 5px 0;
}

.opt {
	display: block;
}

.created {
	 text-align: center;
	 background: #a5c956;
	 color: white;
	 text-shadow: none;
	 padding: 10px 0;
}

.red {
	color: #CC0000;
}
</style>
</head>
<body>
	
	<?php include("../includes/header.php"); ?>
	
	<article class="grid-12 group">
		
		<section class="g-12">
			
			<h1>Survey Questions</h1>

			<?php if (isset($_SESSION['created'])): unset($_SESSION['created']); ?>
			<p class="created">
				Question created; appended to end of survey.
			</p>
			<?php endif; ?>


			<div class="instructions">
				<p>
					Ask only for what is necessary to help members with their callings... but have fun with it, too.
					Some tips:
				</p>
				<b>1.</b> <span class="red"><b>Don't ask for name, email, birthday, or anything else found on the <a href="../editprofile.php">edit profile</a> page.</b> Those are already required.</span><br>
				<b>2.</b> Don't edit a question so drastically that its meaning changes. Don't alienate a question from its answers.<br>
				<b>3.</b> If your ward might put profile pictures or other information on LDS.org, use the survey to ask members if you have their permission.<br>
				<b>4.</b> There is no "undo" button here.<br>
				<b>5.</b> To better handle the turnover at new semesters, consider asking for previous ward and bishop's name.<br>
				<!--<b>3.</b> To better handle turnovers at new semesters:<br>
					&nbsp; &nbsp; <b>a.</b> Users already provide their full names and birth dates, but 
					<br>
					&nbsp; &nbsp; <b>b.</b> Avoid storing sensitive information for long (delete the record number question when you're done with it).
					<br>
					&nbsp; &nbsp; <b>c.</b> Consider asking for previous ward name and bishop's name.
					<br>
				-->
				<b>6.</b> Examples of helpful questions include those related to hobbies and interests, temple recommend expiration dates,
				dinner group preferences, intramural sports, employment status, musical abilities, missionary service, engagement, etc.<br>
				<b>7.</b> Only <i>require</i> the most important questions. And any multiple choice/answer question should have clearly-defined options.<br>
				<b>8.</b> Plan before you make your survey. Right now, questions can't be re-ordered.<br>
				<b>9.</b> The shorter the survey, the happier everyone is!
			</div>


			<h2>Create new question</h2>
			

			<form method="post" action="api/newqu.php">
				<table style="width: 100%; background: #FAFAFA;">
					<tr style="background-color: #DDD;">
						<th>Question</th>
						<th>Type</th>
						<th>Possible answers</th>
						<th>Properties</th>
					</tr>
					<tr>
						<td>
							<input type="text" size="35" name="question">
						</td>
						<td class="text-center">
							<select size="1" name="qtype" class="qtype">
								<option value="<?php echo(QuestionType::FreeResponse); ?>" selected="selected">Free response</option>
								<option value="<?php echo(QuestionType::MultipleChoice); ?>">Multiple choice</option>
								<option value="<?php echo(QuestionType::MultipleAnswer); ?>">Multiple answer</option>
								<option value="<?php echo(QuestionType::YesNo); ?>">Yes/no</option>
								<option value="<?php echo(QuestionType::ScaleOneToFive); ?>">Scale from 1 to 5</option>
								<option value="<?php echo(QuestionType::Timestamp); ?>">Date and time</option>
								<option value="<?php echo(QuestionType::CSV); ?>">List</option>
							</select>
						</td>
						<td class="text-center">
							<!-- These divs are toggled depending on the question type -->
							<div class="mc qu-opt hide">
								Members may only select one:
								<br>
								<span class="opt"><input type='text' name='ans[0]' id='ans0'><br><a href="javascript:" class="newopt">Add another</a></span>
							</div>
							<div class="ma qu-opt hide">
								Member may select multiple:
								<br>
								<span class="opt"><input type='text' name='ans[0]' id='ans0'><br><a href="javascript:" class="newopt">Add another</a></span>
							</div>
							<!-- End toggled divs -->
					</td>
					<td>
						<label><input type="checkbox" name="req"> Required</label>
						<br>
						<label><input type="checkbox" name="visible" checked="checked"> Visible (active)</label>
					</td>
				</tr>
				<tr>
					<td colspan="4" class="text-right">
						<input type="submit" value="Save question" class="button"> &nbsp; &nbsp;
					</td>
				</table>
			</form>

			<br><br><br><br>
			
			<h2>Existing questions</h2>
			
			<p>You may edit one question at a time.</p>	

			<br>
		<?php

			$i = 0;
			while ($row = mysql_fetch_array($r))
			{
				$i ++;
				$sq = SurveyQuestion::Load($row['ID']);
				$ansCount = count($sq->Answers());
				$sqInputSize = strlen($sq->Question) > 45 ? 55 : strlen($sq->Question) + 10;
		?>
			<form method="post" id="<?php echo $sq->ID(); ?>" action="api/savequ.php" class="update-question">
			<table style="width: 100%; background: <?php echo $i % 2 ? "#EFEFEF" : "#FAFAFA"; ?>">
			<input type="hidden" name="qid" value="<?php echo $sq->ID(); ?>">
				<tr style="background-color: #DDD;">
					<th>Question</th>
					<th>Type</th>
					<th>Possible answers</th>
					<th>Properties</th>
				</tr>
				<tr>
					<td>
						<textarea rows="3" cols="25" name="question"><?php echo strip_tags($sq->Question); ?></textarea>
						<br>
						<small><b>Answers:</b> <?php echo $ansCount; ?></small>
					</td>
					<td class="text-center">
						<select size="1" name="qtype" class="qtype">
						<option value="<?php echo(QuestionType::FreeResponse); ?>"<?php if ($sq->QuestionType == QuestionType::FreeResponse) echo' selected="selected"'; ?>>Free response</option>
							<option value="<?php echo(QuestionType::MultipleChoice); ?>"<?php if ($sq->QuestionType == QuestionType::MultipleChoice) echo' selected="selected"'; ?>>Multiple choice</option>
							<option value="<?php echo(QuestionType::MultipleAnswer); ?>"<?php if ($sq->QuestionType == QuestionType::MultipleAnswer) echo' selected="selected"'; ?>>Multiple answer</option>
							<option value="<?php echo(QuestionType::YesNo); ?>"<?php if ($sq->QuestionType == QuestionType::YesNo) echo' selected="selected"'; ?>>Yes/no</option>
							<option value="<?php echo(QuestionType::ScaleOneToFive); ?>"<?php if ($sq->QuestionType == QuestionType::ScaleOneToFive) echo' selected="selected"'; ?>>Scale from 1 to 5</option>
							<option value="<?php echo(QuestionType::Timestamp); ?>"<?php if ($sq->QuestionType == QuestionType::Timestamp) echo' selected="selected"'; ?>>Date and time</option>
							<option value="<?php echo(QuestionType::CSV); ?>"<?php if ($sq->QuestionType == QuestionType::CSV) echo' selected="selected"'; ?>>List</option>
						</select>
					</td>
					<td class="text-center">
						<!-- These divs are toggled depending on the question type -->
						<div class="fr qu-opt<?php echo $sq->QuestionType != QuestionType::FreeResponse ? ' hide' : ''; ?>">
							<i>Free-response</i>
						</div>
						<div class="mc qu-opt<?php echo $sq->QuestionType != QuestionType::MultipleChoice ? ' hide' : ''; ?>">
							<small><i>Members may select only one:</i></small><br>
		<?php foreach ($sq->AnswerOptions() as $ansOpt): ?>
							<span class="opt"><input type="text" name="ans[<?php echo $ansOpt->ID(); ?>]" id="ans<?php echo $ansOpt->ID(); ?>" value="<?php echo htmlentities($ansOpt->AnswerValue()); ?>"> <a href="javascript:" class="delOpt">x</a><br></span>
		<?php endforeach; ?>
							<a href="javascript:" class="newopt">Add another</a>
						</div>
						<div class="ma qu-opt<?php echo $sq->QuestionType != QuestionType::MultipleAnswer ? ' hide' : ''; ?>">
						<small><i>Members may select multiple:</i></small><br>
		<?php foreach ($sq->AnswerOptions() as $ansOpt): ?>
							<span class="opt"><input type="text" name="ans[<?php echo $ansOpt->ID(); ?>]" id="ans<?php echo $ansOpt->ID(); ?>" value="<?php echo htmlentities($ansOpt->AnswerValue()); ?>"> <a href="javascript:" class="delOpt">x</a><br></span>
		<?php endforeach; ?>
							<a href="javascript:" class="newopt">Add another</a>
						</div>
						<div class="yn qu-opt<?php echo $sq->QuestionType != QuestionType::YesNo ? ' hide' : ''; ?>">
							<i>Yes/no</i>
						</div>
						<div class="scale qu-opt<?php echo $sq->QuestionType != QuestionType::ScaleOneToFive ? ' hide' : ''; ?>">
							<i>1&mdash;5</i>
						</div>
						<div class="tm qu-opt<?php echo $sq->QuestionType != QuestionType::Timestamp ? ' hide' : ''; ?>">
							<i>Any date/time value</i>
						</div>
						<div class="csv qu-opt<?php echo $sq->QuestionType != QuestionType::CSV ? ' hide' : ''; ?>">
							<i>Any plain-text list</i>
						</div>
				<!-- End toggled divs -->
				</td>
				<td>
					<label><input type="checkbox" name="req"<?php if ($sq->Required) echo' checked="checked"'; ?>> Required</label>
					<br>
					<label><input type="checkbox" name="visible"<?php if ($sq->Visible) echo' checked="checked"'; ?>> Visible (active)</label>
				</td>
			</tr>
			<tr>
				<td colspan="4">
					<label style="float: left;">
						<input type="checkbox" name="delete"> Delete this question and all answers to it (irrevokable)
					</label>
					<div class="float-right">
						<input type="submit" value="Save changes" class="button"> &nbsp; &nbsp;
					</div>
					<br><br><br><br><br>
				</td>
			</table>
			</form>
		<?php } ?>


		</section>
		
	</article>
	

	
	
<script type="text/javascript">

$(function() {
	
	var ansIdx = 0; // The next index value for the question options
	var formForDivToShow; // The form, or question, which we're working with
	
	// Add another field for the multiple-answer/choice ones
	$('form').on('click', '.newopt', function() {

		// Find an available array index
		while ($('input#ans' + ansIdx, $(this).parent()).length > 0)
			ansIdx ++;
		var newone = '<span class="opt""><input type="text" name="ans['+ansIdx+'] id="ans'+ansIdx+'"> <a href="javascript:" class="delOpt">x</a><br>';
		$(newone).insertBefore(this);
		ansIdx ++;
	});
	
	// Toggle new question options
	$('.qtype').change(function() {
		var val = $(this).val();
		formForDivToShow = $(this).closest('form');
		var divToShow;
		
		if (val == <?php echo QuestionType::FreeResponse; ?>)
			divToShow = 'fr';
		else if (val == <?php echo QuestionType::MultipleChoice; ?>)
			divToShow = 'mc';
		else if (val == <?php echo QuestionType::MultipleAnswer; ?>)
			divToShow = 'ma';
		else if (val == <?php echo QuestionType::YesNo; ?>)
			divToShow = 'yn';
		else if (val == <?php echo QuestionType::ScaleOneToFive; ?>)
			divToShow = 'scale';
		else if (val == <?php echo QuestionType::Timestamp; ?>)
			divToShow = 'tm';
		else if (val == <?php echo QuestionType::CSV; ?>)
			divToShow = 'csv';
		else
			divToShow = 'na';
		
		// Hide any other options div
		$('.qu-opt', formForDivToShow).hide();
		
		if (divToShow !== '')
			$('.' + divToShow, formForDivToShow).show();
	});
	
	// Delete an answer option from the DOM
	$('.qu-opt').on('click', '.delOpt', function() {
		$(this).parent().remove();
	});


	// This binding must go BEFORE our hijax below...
	$('form').submit(function() {
		// Remove these from the DOM so, if there are any,
		// they don't confuse the server
		$('.qu-opt').not(':visible').remove();
		$('input[type=submit]', this).prop('disabled', true);
	});
	

	// User updates a question
	$('.update-question').hijax({
		complete: function(xhr)
		{
			if (xhr.status == 200)
			{
				if ($('input[name=delete]:checked', this).length > 0)
				{
					// Deleted the question
					this.fadeOut('medium');
				}

				toastr.success("Changes saved");
			}
			else
				toastr.error(xhr.responseText || "Something went wrong; couldn't save survey question.");

			$('input[type=submit]', this).prop('disabled', false);
		}
	});
	

	// Fade out any "created" success messages
	setTimeout(function() {
		$('.created').fadeOut(4000);
	}, 2000);
});

</script>
<?php include("../includes/footer.php"); ?>