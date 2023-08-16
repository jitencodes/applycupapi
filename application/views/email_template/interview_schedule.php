<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interview Sechudule</title>
</head>

<body>
    <div>Hi <?= $candidate_name ?>,<br><br>An interview has been rescheduled. Details are stated below.<br>Job Role: <strong><?= $job_role ?></strong><br>Company: <strong><?= $company_name ?></strong><br>Interview type: <strong><?= $interview_type ?></strong><?php if ($interview_mode == 2) { ?>
            <br>Address: <strong><?= $interview_input ?></strong>
        <?php } elseif ($interview_mode == 3) { ?>
            <br>Google Meet: <strong><?= $interview_input ?></strong>
        <?php } ?> <br>Interview date and time: <strong><?= $interview_datetime ?></strong><br><br><strong>Please be on time for the interview. Reply to this thread in case you are not available or you would like to get the interview rescheduled.</strong><br><br>Best Regards,<br>Hiring Team<br><?= $company_name ?>
    </div>
</body>
</html>