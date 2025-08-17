<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/Constants.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Archive - Dashboard</title>
	<link href="<?php echo $webURL;?>reckStatic/css/bootstrap.min.css" rel="stylesheet"  crossorigin="anonymous">
    <link rel="stylesheet" href="<?php echo $webURL;?>reckStatic/css/gallery.css">
    <style>
        /* td, th {
            width:150px !important;
            height:75px !important;
            max-width:150px !important;
            max-height:75px !important;
            min-width:150px !important;
            min-height:75px !important;
        } */

        audio{
            max-width: 100%;
            max-height: 70vh;
            object-fit: contain;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        video, img {
            max-width: 100%;
            max-height: 70vh;
            height: auto;
            width: auto;
            object-fit: contain;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .pagination {
            justify-content: center;
        }
    </style>
</head>
<body>

	<div class="">
         <table class="table table-hover">
            <thead>
            <tr>
                <th class="col-5">Media</th>
                <th class="col-4">Caption</th>
                <th class="col-3">Action</th>
            </tr>
            </thead>
            <tbody id="media-list-admin">
            </tbody>
        </table>

        <div>
            <input type="hidden" name="pagenum" id="pagenum">
            <ul id="page-nav" class="pagination"></ul>
        </div>

        <div class="loader"></div>

        <!-- modal -->
        <div class="modal fade" id="editFileModal" tabindex="-1" aria-labelledby="editFileModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Please confirm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                    <button type="button" id="confirm-link" class="btn btn-danger">Confirm</button>
                </div>
                <div class="confirm-msg"></div>
                </div>
            </div>
        </div>
        <!-- modal -->

	</div>
   <script>
        const webURL = "<?php echo $webURL;?>";
    </script>
    <script src="<?php echo $webURL;?>reckStatic/js/jquery-3.4.1.min.js"></script>
	<script src="<?php echo $webURL;?>reckStatic/js/bootstrap.bundle.min.js"  crossorigin="anonymous"></script>
	<script src="<?php echo $webURL;?>reckStatic/js/wavesurfer.min.js"></script>

	<script src="<?php echo $webURL;?>reckStatic/js/common.js"  crossorigin="anonymous"></script>
	<script src="<?php echo $webURL;?>reckStatic/js/UI-user.js"  crossorigin="anonymous"></script>
	
    <script>
        $(document).ready(async function () {
		    const AppState = {
                mediaList : document.getElementById("media-list-admin"),
                paginationBar : document.getElementById("page-nav"),
    			set imageDone(value) {
                    this.mediaList.innerHTML = "";
                }
            }

            listAdminMediaFiles(page = 'page1').then(
                async function(result){
                    $('.loader').show();
                    await addDataToDom(result)                 
                },
                function(error){
                    $('.loader').hide();
                    console.log(error)
                }
            )

            page = page.split('page');
            $('#pagenum').val(page[1]);
            getPaginationBar().then(
                async function(result){
                //   console.log(result);  
                  await addDataToPaginationBar(result)
                }
            )

            $(document).on("click", ".delete-action-btn", function(){
                $('#confirm-link button').attr('class','btn-primary')
                $('.confirm-msg').html('');
                let mediaId = $(this).attr('row-id');
                if($(this).attr('class') == 'delete-action-btn'){
                    let status = $(this).attr('row-action');
                    $('.modal-body').html('<div>Are you sure you want to delete?</div>')
                    $('#confirm-link').attr('row-id',`${mediaId}`)
                    $('#confirm-link').attr('row-action',`${status}`)
                    $('#confirm-link button').attr('class','btn-danger')
                }
            });

            $(document).on("click","#confirm-link",async function(){
                let mediaId = status = 0;
                mediaId = $(this).attr('row-id');
                status = $(this).attr('row-action');
                rowId = "#media-id-"+mediaId; 
                if(mediaId == 0 || status == 0 || status == 'undefined'){
                    $('.confirm-msg').html(`<p class="text-red text-center"> Something went wrong.</p>`)
                    return;
                }

                //status 1 - active, 2 - inactive, 3 - delete
                const res = await editMedia(mediaId, status);
                if(res && res.status == 1){

                    $(rowId).slideUp("slow", function() {
                        $(rowId).remove();
                    });
                    $('.confirm-msg').html(`<p class="text-green text-center"> ${res.msg}</p>`)
                }else
                $('.confirm-msg').html(`<p class="text-red text-center"> ${res.msg}</p>`)
            });

        });
    </script>
</body>
</html>