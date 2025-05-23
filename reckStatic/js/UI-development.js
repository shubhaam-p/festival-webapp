$(document).ready (function () {  

delayInSec = 10000;

function onclickError(clickid) {
    $('#'+clickid).show();
    $('#submit_load').remove();
}

function onclickLoading(clickid) {
    $('#'+clickid).hide();
    $('<div id="submit_load"><image width="18" height="18" src="'+webURL+'reckStatic/loading.gif"/></div>').insertAfter($('#'+clickid));
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

async function makeAjaxCall({url, method = "GET", data}) {
    return new Promise((resolve, reject) => {
        const isFormData = data instanceof FormData;
        const ajaxOptions = {
            url: url,
            method: method,
            data: data,
            processData: !isFormData,
            contentType: isFormData ? false : "application/x-www-form-urlencoded; charset=UTF-8",
            success: function(data) {
                resolve(JSON.parse(data));
            },
            error: function(error) {
                reject(error);
            },
        };
        $.ajax(ajaxOptions).fail(function(error) {
            reject(error);
        });
    });
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
        if (isCookieSet('form_submitted') || (isCookieSet('media_count') && getCookie('media_count')>=4)){
            throw 'Form is already submitted. Thank you!';
        }
        onclickLoading('submitFormButton');
        $('.submit-response-msg').empty()
        $('.submit-response-msg').show();
        // Constants for XML parameters
        const XML_PARAMETER_AUTHORNAME = "author";
        const actionURL = 'addMedia';

        let msg = ``;
        let authorName = $("#authorName").val();
        let files = document.getElementById("imageInput").files;

        const queryXML = `<?xml version='1.0'?>`
            + `<query>`
            + `<action>${actionURL}</action>`
            + getXMLString(XML_PARAMETER_AUTHORNAME, () => authorName)
            + `</query>`;

        let formData = new FormData();
        formData.append("xmlData", queryXML);
        formData.append("action", actionURL);

        for (let i = 0; i < files.length; i++) {
            // formData.append("media[]", files[i]); // use same name (media[]) for PHP array
            formData.append("file[]", files[i]);
        }
        await makeAjaxCall({
        url: `${webURL}/new-cont-reg`,
        method: "POST",
        data: formData,
        }).then((res)=>{
            console.log("response ",res);
            if (res.status === 1) {
                // $("#uploadMediaForm")[0].reset();
                $("#imageInput").val(null)
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
                $('.submit-response-msg').empty().show().html(`<b class='fs-16 text-red'>Error while submitting the form!!<b> ${msg}`).delay(delayInSec).fadeOut(300);
            } 
            onclickError('submitFormButton');
        })
        }catch(err){
                msg = `<div>${err}</div>`;
                $('.submit-response-msg').empty().show().html(`${msg}`).delay(delayInSec).fadeOut(300);
        }
    }  
});

});