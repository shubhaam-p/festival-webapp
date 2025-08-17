async function listGalleryMediaFiles(calledAgain = false, page = 0){  
    let pagination = '';

    if(calledAgain && page > 0)
        pagination = `&page=${page}`;

    let res = await makeAjaxCall({
        url: `${webURL}/new-cont-reg?Type=Gall3ry&action=listMedia&home=1${pagination}&ts=${Date.now()}`,
        method: "GET",
        });
    return res; 
}

//For admin page
async function listAdminMediaFiles(page = 'page1', addToPage = false){
    // console.log("clicked file", page, addToPage)
    let pagination = '1';
    let pageNo = page.split('page')[1];
    $('#pagenum').val(pageNo);

    if(pageNo > 0)
        pagination = `&page=${pageNo}`;

    if(addToPage)
        getPaginationBar(pageNo, true)

    let res = await makeAjaxCall({
        url: `${webURL}/new-cont-reg?Type=Gall3ry&action=listMedia&admin=1${pagination}&ts=${Date.now()}`,
        method: "GET",
        });

    if(addToPage){
        $('.loader').show()
        $('#media-list-admin').hide();
        addDataToDom(res)
    }else
        return res; 
}

//For admin page status - (3-delete)
async function editMedia(mediaId = 0, status = 0){  
        let nid = mediaId;
        let remove = changestatus = 0;
        if(status == 3)
            remove = 1;
        else
            return 'invalid action';

    let res = await makeAjaxCall({
        url: `${webURL}/new-cont-reg?Type=Gall3ry&nid=${nid}&action=editMedia&remove=${remove}&admin=1&ts=${Date.now()}`,
        method: "GET",
        });
    return res; 
}

async function getPaginationBar(page = 1, addToPage = false){  
    // console.log("pagination bar ",page, addToPage);
    let pageNo = page;

    let res = await makeAjaxCall({
        url: `${webURL}/new-cont-reg?Type=Gall3ry&page=${pageNo}&action=getPaginationBar&admin=1&ts=${Date.now()}`,
        method: "GET",
        });

    if(addToPage){
        $('#page-nav').hide();
        addDataToPaginationBar(res)
    }else
        return res; 
}

async function addDataToDom(result){
    let mediaList = document.getElementById("media-list-admin");
    // console.log(result);            
    if(result.total <= 0)
    mediaList.innerHTML = "<br>Data not found!<br>"
   
    mediaList.innerHTML = ''; 
    let tr = file = mimeType = cls = '';
    result.data.forEach((element, i, array) => {
        tr = document.createElement("tr");
        tr.id = "media-id-"+element.ID;

        mimeType = result.data[i].MIMETYPE.split('/');
        file = '';
        switch(mimeType[0]){
            case 'image':
                file = `<img src="${element.MEDIA}" class="${cls}" data-type="image" loading="lazy">`;
            break;

        case 'video':
            file = `<video src="${element.MEDIA}" class="${cls}" data-type="video" controls></video>`;
            break;
    
        case 'audio':
            file = `<audio src="${element.MEDIA}" class="${cls}" data-type="audio" controls></audio>`;
            break;
        }

        tr.innerHTML = `<td>${i+1}</td>
                        <td>${file}</td>
                        <td>${element.CAPTION}</td>
                        <td>
                            <span title="Delete" class="delete-action-btn"  data-bs-toggle="modal" data-bs-target="#editFileModal" data-toggle="modal" data-target=".confirm-modal" row-id="${element.ID}" row-action="3">
                                <button type="button" class="btn btn-outline-primary">Delete</button>
                            </span>
                        </td>`;

        mediaList.appendChild(tr);
    });
    $('.loader').hide();
    $('#media-list-admin').fadeIn('slow');
}

function addDataToPaginationBar(result){
    let paginationBar = document.getElementById("page-nav");

    if(result.status == 1){
        $('#page-nav').fadeIn('slow');
        paginationBar.innerHTML = (result.data);
    }
}