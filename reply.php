<?php
$taskId = $taskInfo->taskId;
$taskTitle = $taskInfo->taskTitle;
$description = $taskInfo->description;
$status = $taskInfo->status;
$taskattchS3File = $taskInfo->taskattchS3File;
$collabrators = $taskInfo->collabrators;

$selectUserId = '';
?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <i class="fa fa-user-circle-o" aria-hidden="true"></i> Task Management
            <small>Reply Task</small>
        </h1>
    </section>

    <section class="content">
        <div class="row">
            <!-- left column -->
            <div class="col-md-9">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">Task Reply</h3>
                    </div><!-- /.box-header -->
                    <!-- form start -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="taskTitle">Task Title</label>
                                    <input type="text" class="form-control required" value="<?php echo $taskTitle; ?>" id="taskTitle" name="taskTitle" maxlength="256" readonly>
                                    <input type="hidden" value="<?php echo $taskId; ?>" name="taskId" id="taskId" />
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">

                                    <label for="collaborators">Collaborators <span class="re-mend-field"></span></label>
                                    <?php
                                    if (!empty($users)) {
                                        // Assuming $collabrators is a comma-separated string of userIds
                                        $selectedCollaborators = explode(',', $collabrators);

                                        $collaboratorNames = [];
                                        foreach ($users as $rl) {
                                            if (in_array($rl->userId, $selectedCollaborators)) {
                                                $collaboratorNames[] = htmlspecialchars($rl->name);
                                            }
                                        }

                                        // Join all collaborator names with a comma
                                        $collaboratorNamesText = implode(', ', $collaboratorNames);
                                    } else {
                                        $collaboratorNamesText = "No collaborators found.";
                                    }
                                    ?>
                                    <input type="text" class="form-control" id="collaborators-field" value="<?= $collaboratorNamesText ?>" readonly>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group reply-desc-cls">
                                        <label for="description">Description</label>
                                        <?php echo $description; ?><br>
                                    </div>
                                    <div class="form-group reply-desc-cls">
                                        <label for="created">Created At</label>
                                        <?php
                                        $query = $this->db->query("SELECT createdDtm FROM tbl_task WHERE taskId = " . $taskId);
                                        if ($query->num_rows() > 0) {
                                            $row = $query->row();
                                            echo $row->createdDtm;
                                        } else {
                                            echo "No record found";
                                        }
                                        ?><br>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="taskTitle">File Attachment</label>
                                            <a href="<?php echo $taskInfo->taskattchS3File ?>" target="_blank"><button class="btn"><img src="<?php echo $taskInfo->taskattchS3File ?>" style="height: 50px !important;width: 50px;"></a>
                                        </div>
                                    </div>

                                    <div class="col-md-12 reply-desc-cls" id="repliesList">
                                        <?php
                                        if (!empty($replyData)) {
                                            foreach ($replyData as $replyDat) {
                                                $Query          = "SELECT * FROM tbl_users WHERE userId='$replyDat->repliedBy'";
                                                $fetchAr        = $this->db->query($Query);
                                                $Details        = $fetchAr->row();
                                                $replyName      = $Details->name;
                                        ?>
                                                <span style="color: gray;font-size: 12px;"><i class="fa fa-user"></i> <?php echo $replyName ?> :-</span>
                                                <p><?php echo $replyDat->reply ?></p>
                                                <p> <?php echo $replyDat->createdDtm; ?></p>


                                        <?php
                                            }
                                        }
                                        ?>
                                    </div>


                                    <div class="col-md-12">
                                        <button class="btn btn-sm btn-info" title="Reply" onclick="myFunction()"><i class="fa fa-reply"></i> Reply</button>
                                        <a href="<?php echo base_url() ?>task/taskListing" class="btn btn-sm btn-info">Back to List</a>
                                    </div>

                                    <!-- Reply Form -->
                                    <form role="form" id="replyTaskForm" enctype="multipart/form-data">
                                        <input type="hidden" value="<?php echo $taskId; ?>" name="taskId" id="taskId" />
                                        <div class="col-md-12 shodiv" style="margin-top: 13px;">
                                            <div class="form-group">
                                                <textarea class="form-control required" id="taskReply" name="taskReply" placeholder="Type your reply here..."></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label for="file">Upload File</label>
                                                <input type="file" name="file[]" id="file" multiple>
                                            </div>
                                            <input type="submit" class="btn btn-sm btn-primary" value="Submit" />
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </section>
</div>
<style type="text/css">
    textarea.form-control {
        height: 250px;
    }

    .reply-desc-cls p {
        word-wrap: break-word;
    }

    .reply-desc-cls .table td {
        word-wrap: break-word;
    }
</style>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/common.js" charset="utf-8"></script>
<!-- <script src="https://cdn.ckeditor.com/ckeditor5/39.0.0/classic/ckeditor.js"></script> -->
<script type="text/javascript">
    $('.shodiv').hide();

    function myFunction(params) {
        $('.shodiv').show();
    }
</script>
<script>
    ClassicEditor
        .create(document.querySelector("#taskReply"))
        .catch(error => {
            console.er
            ror(error);
        });
</script>
<script src="<?php echo base_url('assets/js/jquery.min.js'); ?>"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="<?php echo base_url('assets/js/jquery.min.js'); ?>"></script>
<script>
    if (typeof jQuery === 'undefined') {
        document.write('<script src="https://code.jquery.com/jquery-3.6.0.min.js"><\/script>');
    }
</script>
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.0/classic/ckeditor.js"></script>
<script type="text/javascript">
let ckEditorInstance;
let uploadedFiles = []; // Array to store uploaded file URLs

// Initialize CKEditor
ClassicEditor.create(document.querySelector('#taskReply'))
    .then(editor => {
        ckEditorInstance = editor; // Save editor instance for future use
    })
    .catch(error => {
        console.error('Error initializing CKEditor:', error);
    });

// Handle form submission
$('#replyTaskForm').submit(function (e) {
    e.preventDefault();

    // Get input values
    const replyText = ckEditorInstance.getData().trim();
    const fileInput = $('#file')[0].files;

    // Validation: Ensure at least one field is filled
    if (!replyText && fileInput.length === 0 && uploadedFiles.length === 0) {
        alert('Please provide a reply text or upload a file before submitting.');
        return;
    }

    let formData = new FormData(this); // Collect form data
    formData.set('taskReply', replyText);

    // Append already uploaded files
    uploadedFiles.forEach((file, index) => {
        formData.append(`uploadedFiles[${index}]`, file);
    });

    $.ajax({
        url: '<?php echo base_url("task/replyTask") ?>',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
            console.log('Response:', response);
            response = JSON.parse(response);

            if (response.success) {
                appendReply(response); // Append new reply
                uploadedFiles = []; // Reset uploaded files on success
                saveRepliesToLocalStorage(); // Save updated replies to localStorage
                clearInputs();
            } else {
                alert(response.message || 'Error submitting reply.');
            }
        },
        error: function (xhr, status, error) {
            console.error('Error:', xhr.responseText || error);
            alert('An error occurred while submitting the form.');
            // dsf
        }
    });
});

// Fetch and render replies from server or localStorage on page load
$(document).ready(function () {
    const taskId = $('#taskId').val();
    fetchReplies(taskId);
});

// Fetch replies from the server or localStorage
function fetchReplies(taskId) {
    const savedReplies = localStorage.getItem(`repliesList_${taskId}`);
    if (savedReplies) {
        $('#repliesList').html(savedReplies);
        restoreUploadedFilesFromReplies();
        return;
    }

    // Fetch replies from server if not in localStorage
    $.ajax({
        url: '<?php echo base_url("task/getReplies") ?>',
        type: 'GET',
        data: { taskId },
        success: function (response) {
            console.log('Replies fetched:', response);
            response = JSON.parse(response);

            if (response.success) {
                const replies = response.replies;
                $('#repliesList').empty(); // Clear existing replies
                replies.forEach(reply => appendReply(reply));
                // saveRepliesToLocalStorage(); // Save replies to localStorage
            } else {
                console.warn('No replies found.');
            }
        },
        error: function (xhr, status, error) {
            console.error('Error fetching replies:', xhr.responseText || error);
        }
    });
}   

// Append a new reply to the list
function appendReply(reply) {
    let newReply = `<div class="reply">
        <strong>${reply.username}:</strong> ${reply.reply}
        <small>${reply.createdDtm}</small>
        <div>`;

    // Add attached images
    if (reply.taskreplyattchS3File && reply.taskreplyattchS3File.length > 0) {
        reply.taskreplyattchS3File.forEach(function (file) {
            newReply += `<a href="${file}" target="_blank">
                <img src="${file}" alt="attachment" style="width: 90px; height: auto; margin-right: 10px;">
            </a>`;
            if (!uploadedFiles.includes(file)) {
                uploadedFiles.push(file); // Add file to the uploaded files array
            }
        });
    }

    newReply += `</div></div>`;
    $('#repliesList').append(newReply);
}

// Save replies to localStorage
function saveRepliesToLocalStorage() {
    const taskId = $('#taskId').val();
    const repliesHtml = $('#repliesList').html();
    localStorage.setItem(`repliesList_${taskId}`, repliesHtml);
}

// Restore uploaded files from localStorage replies
function restoreUploadedFilesFromReplies() {
    $('#repliesList img').each(function () {
        const fileUrl = $(this).attr('src');
        if (!uploadedFiles.includes(fileUrl)) {
            uploadedFiles.push(fileUrl);
        }
    });
}

// Clear CKEditor content and file input
function clearInputs() {
    ckEditorInstance.setData(''); // Clear CKEditor content
    $('#file').val(''); // Clear file input (but retain uploaded files)
}
</script>


<!-- $(document).ready(function() {
        const taskId = $('#taskId').val(); // Assuming taskId is available

        // Fetch replies from localStorage based on taskId
        const savedReplies = localStorage.getItem(`repliesList_${taskId}`);
        if (savedReplies) {
            $('#repliesList').html(savedReplies);
        } -->


        <!-- $(document).ready(function() {
        const taskId = $('#taskId').val(); // Assuming taskId is available

        // Fetch replies from localStorage based on taskId
        const savedReplies = localStorage.getItem(`repliesList_${taskId}`);
        if (savedReplies) {
            $('#repliesList').html(savedReplies);
        }
}); -->


