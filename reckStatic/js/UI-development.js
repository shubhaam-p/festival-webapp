$(document).ready (function () {  

delayInSec = 10000;

function onclickError(clickid) {
    $('#'+clickid).show();
    $('#submit_load').remove();
}

function onclickLoading(clickid) {
    $('#'+clickid).hide();
    $('<div id="submit_load"><image width="18" height="18" src="'+webURL+'reckStatic/images/loading.gif"/></div>').insertAfter($('#'+clickid));
}

function handleLoadingBtnIssue(btnName='', waitTime=10000 ){  
    if( btnName !='' ){
        setTimeout( function(){ 
            $('#'+btnName).show();
            $('#'+btnName).next('#submit_load').remove();
        }, waitTime);
    }
}

// Helper function to generate XML string
function getXMLString(parameter, valueFn) {
    return `&lt;${parameter}&gt;&lt;![CDATA[${valueFn()}]]&gt;&lt;/${parameter}&gt;`;
}

function isCookieSet(cookieName) {
  return document.cookie.split('; ').some(cookie => cookie.startsWith(cookieName + '='));
}

function getCookie(name) {
  const cookies = document.cookie.split(';');
  for (let i = 0; i < cookies.length; i++) {
    let cookie = cookies[i].trim();
    if (cookie.startsWith(name + '=')) {
      return cookie.substring(name.length + 1);
    }
  }
  return null;
}

$('form[id="uploadMediaForm"]').validate({  
    rules: {  
        authorName: 'required',
        file: 'required'
    },
    messages: {  
        authorName: 'This field is required',
        file: 'This field is required',
    },
    submitHandler: async function(form) {
        try{
        let isAdmin = parseInt($('#adminAccess').val()); 
        if( isAdmin === 0)
            if (isCookieSet('form_submitted') || (isCookieSet('media_count') && getCookie('media_count')>=4)){
                throw 'Form is already submitted. Thank you!';
            }
        
        onclickLoading('submitFormButton');
        $('.submit-response-msg').empty()
        $('.submit-response-msg').show();
        // Constants for XML parameters
        const XML_PARAMETER_AUTHORNAME = "author";
        const XML_PARAMETER_CAPTION = "caption";
        const XML_PARAMETER_ADMIN = "admin";
        const actionURL = 'addMedia';

        let msg = ``;
        let videoMetaData = '';
        let videoFileCount = 0;
        let authorName = $("#authorName").val();
        let caption = $("#caption").val();
        let files = document.getElementById("imageInput").files;

        let queryXML = `<?xml version='1.0'?>`
            + `<query>`
            + `<action>${actionURL}</action>`
            + getXMLString(XML_PARAMETER_AUTHORNAME, () => authorName)
            + getXMLString(XML_PARAMETER_CAPTION, () => caption)
            + getXMLString(XML_PARAMETER_ADMIN, () => isAdmin)
            + `</query>`;

        let formData = new FormData();
        for (let i = 0; i < files.length; i++) {
            if (files[i] && files[i].type.startsWith('video/')) {
                videoMetaData = await getVideoMetadata(files[i]);
                console.log("Width:", videoMetaData.width);
                console.log("Height:", videoMetaData.height);
                console.log("Duration:", videoMetaData.duration);
               
                formData.append(`width_${i}`, videoMetaData.width);
                formData.append(`height_${i}`, videoMetaData.height);
                formData.append(`duration_${i}`, videoMetaData.duration);
                // formData.append(`video_${i}`, files[i]);
                videoFileCount++;
            }
            formData.append("file[]", files[i]);
        }


        formData.append("xmlData", queryXML);
        formData.append("action", actionURL);
        if(videoFileCount > 0)
            formData.append("videoFileCount", videoFileCount);

        await makeAjaxCall({
            url: `${webURL}/new-cont-reg`,
            method: "POST",
            data: formData,
        }).then((res)=>{
            console.log("response ",res);
            if (res.status === 1) {
                // $("#uploadMediaForm")[0].reset();
                $("#imageInput").val(null)
                $("#caption").val(null)
                console.log("reset form")
                if(res.data.length>0){
                    res.data.forEach((i)=>{
                        if(i.status == 1)
                            msg +=`<div>${i.message} ${i.data}</div>`;
                        else
                            msg +=`<div class='fs-16 text-red'>${i.message}</div>`;
                    })
                }
                console.log("msg ",msg);
                $('.submit-response-msg').empty().show().html(`<b class='fs-16 text-success'>${msg}<b>`).delay(delayInSec).fadeOut(300);
                
            } else{
                msg = `<div>${res.message}</div>`;
                $('.submit-response-msg').empty().show().html(`<b class='fs-16 text-red'>Error while submitting the form!!<b> <div>${msg}</div>`).delay(delayInSec).fadeOut(300);
            } 
            onclickError('submitFormButton');
        })
        }catch(err){
                msg = `<div>${err}</div>`;
                $('.submit-response-msg').empty().show().html(`${msg}`).delay(delayInSec).fadeOut(300);
        }
    }  
});

function getVideoMetadata(file) {
  return new Promise((resolve, reject) => {
    const video = document.createElement("video");

    video.preload = "metadata";
    video.src = URL.createObjectURL(file);

    video.onloadedmetadata = () => {
      URL.revokeObjectURL(video.src); // Clean up memory

      resolve({
        width: video.videoWidth,
        height: video.videoHeight,
        duration: video.duration
      });
    };

    video.onerror = () => {
      reject(new Error("Invalid video file"));
    };
  });
}


});