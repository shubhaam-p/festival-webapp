<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/Constants.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Gallery</title>
	<link href="<?php echo $webURL;?>reckStatic/css/bootstrap.min.css" rel="stylesheet"  crossorigin="anonymous">
    <link rel="stylesheet" href="<?php echo $webURL;?>reckStatic/css/gallery.css">
</head>
<body>

	<div class="container">
		<div class="row g-4">
            <div id="media-list"></div>
		</div>
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
		const mediaList = document.getElementById("media-list");
		mediaList.innerHTML = "";
		let result = cls = '';
		let startY = 0;
		let isScrolling = false;
		let isPinching = false;
		let pauseScrollTimeInSec = 2000;//Pause the scrolling after media scrolled
		let fetchMediaFnCalled = 0;//Wait till media is listed in dom, before next call -- Additional flag
		const callFechFnBeforeNoOfFiles = <?=$CONST_FETCHFN_BEFORE_QUEUE_ENDS?>
	
		const AppState = {
			slides: {},
			wavesurfer: new Map(),
			pageNo: 0,//For pagination
			isLastPage: 0,//Flag is used for letting user that he scrolled upto end 
			endOfSlideShown: 0,//Flag if end slide shown after last media
			current: 0,
			isFirstSlideAdded: 0,//Flag used to add active class
		};

		let obj = {
			imageDoneValue: 0,
			totalMediaCountValue: NaN,
			letMeKnow() {
				console.log('total ',obj.totalMediaCount);
				if(!isNaN(obj.totalMediaCount) && obj.totalMediaCount <=0){
					
					AppState.slides = {};
					AppState.wavesurfer = new Map();
					AppState.slides = document.querySelectorAll('.media-slide');
					addLoader();
					//Reseting the flag
					fetchMediaFnCalled = 0;
				}
			},
			get imageDone() {
				return this.imageDoneValue;
			},
			set imageDone(value) {
				this.imageDoneValue = value;
				this.letMeKnow();
			},
			get totalMediaCount() {
				return this.totalMediaCountValue;
			},
			set totalMediaCount(value) {
				this.totalMediaCountValue = value;
				this.letMeKnow();
			}
		}

		//Initial call - Fetch media
		await callListGalleryMediaFiles();

		async function showNextSlide(){
			if (AppState.current < AppState.slides.length - 1) {
				if(AppState.isLastPage === false && (AppState.slides.length - 1 - AppState.current) == callFechFnBeforeNoOfFiles && fetchMediaFnCalled == 0){
					fetchMediaFnCalled = 1;
					obj.totalMediaCount = NaN;
					//Pagination call - Fetch media
					await callListGalleryMediaFiles(1, AppState.pageNo);
				}
				
		    	const datatype = $(AppState.slides[AppState.current]).find('.audio-wrapper').attr('data-type') ?? '';
				switch (datatype) {
					case 'audio':
						let id = $(AppState.slides[AppState.current]).find('.audio-wrapper').attr('data-id') ?? 0;
						id = id!==0? parseInt(id):0;
						const instance = AppState.wavesurfer.get(id);
						if (instance && id) {
							instance.destroy();
							AppState.wavesurfer.delete(id);
							console.log(`Destroyed WaveSurfer: ${id}`, AppState.wavesurfer);
						}
						break;
				
					default:
						console.log("default switch")
						break;
				}

				//Added this after switch block because, switch was deleting next instance rather than current
				AppState.slides[AppState.current].classList.remove('active');
				AppState.current++;
				AppState.slides[AppState.current].classList.add('active');
				AppState.slides[AppState.current].scrollIntoView({ behavior: "smooth", block: "start" });
			} else if(AppState.isLastPage === true && AppState.endOfSlideShown === 0){
				AppState.endOfSlideShown = 1
				console.log("You've seen all of posts!");
			} else {
				// End of media
				console.log("You've reached the end of the exhibition!");
			}
		}

		// Swipe up detection (basic touch event)
		window.addEventListener('touchstart', (e) => {
			if (e.touches.length > 1) {
				isPinching = true; // Pinch gesture
			} else {
				isPinching = false;
				startY = e.touches[0].clientY;
			}
		});

		window.addEventListener('touchend', (e) => {
			if(isScrolling || isPinching) return;

			const endY = e.changedTouches[0].clientY;
			const deltaY = startY - endY;

			if (deltaY > 50) {
				showNextSlide();
				console.log("touchend - swipe")
				setTimeout(()=>{
					isScrolling = false;
				}, pauseScrollTimeInSec);
			}
		});

		// Scroll down detection for non-touch devices
		window.addEventListener('wheel', (e) => {
			if(isScrolling) return;
			if (e.deltaY > 20) {
				isScrolling = true;
				console.log("wheel - swipe")
				showNextSlide();	
			}
			setTimeout(()=>{
				isScrolling = false;
			}, pauseScrollTimeInSec);
			
			}, { passive: true }
		);

		//Zoom
		const images = document.querySelectorAll('.zoomable');
		images.forEach(img => {
			img.addEventListener('dblclick', (e) => {
				const isZoomed = img.classList.contains('img-zoom');
				// Remove zoom from all images
				images.forEach(i => i.classList.remove('img-zoom'));

				if(!isZoomed){
					const rect = img.getBoundingClientRect();
					const offsetX = e.clientX - rect.left;
					const offsetY = e.clientY - rect.top;
		
					img.style.transformOrigin = `${offsetX}px ${offsetY}px`;
					img.classList.toggle('img-zoom');
				}
			});
		});

		//Fetch images
		async function callListGalleryMediaFiles(calledAgain = 0, pageNo = 0){
			await listGalleryMediaFiles(calledAgain, (pageNo+1)).then(async (result)=>{
				if(result?.total == 0){
					let div = document.createElement("div");
					div.innerHTML = '<div>Packages are currenly unavailable!</div>';
					mediaList.append(div);
				}else{
					let mediaFile = result.data;
					obj.totalMediaCount = result.total;
					AppState.pageNo = parseInt(result.page);		
					AppState.isLastPage = result.lastPage;

					mediaFile.forEach((element, i, array) => {
						
					let isAudio = 0;	
					let div = document.createElement("div");
					div.classList.add("media-slide", "col-sm-12", "col-md-6", "col-lg-6", "media-container");
					if(AppState.isFirstSlideAdded === 1 && calledAgain == 0)
						div.classList.add("active");

					let cls = mediaFile[i].CLASS !='' || mediaFile[i].CLASS != 'undefined'? mediaFile[i].CLASS:'';
					let mimeType = mediaFile[i].MIMETYPE.split('/');
					let file = ''; 
					switch(mimeType[0]){
						case 'image':
							file = `<img src="${mediaFile[i].MEDIA}" class="${cls}" data-type="image" loading="lazy">`;
							break;

						case 'video':
							file = `<video src="${mediaFile[i].MEDIA}" class="${cls}" data-type="video" controls></video>`;
							break;

						case 'audio':
							isAudio = 1;
							// let screenwidth = window.innerWidth;
							file = `
									<div class="audio-wrapper" data-id="${mediaFile[i].ID}" data-type="audio">
										<button id="playPause-${mediaFile[i].ID}" class="playPause" data-id="${mediaFile[i].ID}">
											<img src="${webURL}reckStatic/images/play.png" alt="play">
										</button>
										<div class="audio-container" style="width:400px">
											<div id="waveform-${mediaFile[i].ID}" class="waveform" style="display: none;"></div>
										</div>
									</div>`;

							break;
						
						case 'last':
							file = "<p>You've seen all of posts!</p>";

						default:
							console.log('unspported format');
					}
	
						// Append images to row
						div.innerHTML = file;
						mediaList.appendChild(div);
						
						if(isAudio == 1){
							createWavesOfAudio(mediaFile[i].MEDIA, mediaFile[i].ID);
						}

						AppState.isFirstSlideAdded++;
						obj.totalMediaCount--;
					});
				}
			});
		}

		function createWavesOfAudio(audioURL, ID = 0){
			ID = parseInt(ID);
			const options = {
				container: 'id',
				waveColor: '#ababab',
				progressColor: '#0025d1',
				cursorColor:'#ddd5e9',
				cursorWidth: 2,
				height:200,
				interact: true,
				barWidth: 3,
				barGap: 1,
				barRadius: 2,
				url: "URL",    
			}

			options.url= audioURL
			options.container='#waveform-'+ID;
			const wsInstance =  WaveSurfer.create(options);
			AppState.wavesurfer.set(ID, wsInstance);

			loadingText = '<p css="loading" style="height:20px; width=119px;">Loading...</p>'
			$('.waveform').css('display','none')
			$(loadingText).insertAfter('.waveform')
			
			wsInstance.on('ready',()=>{
				$('#waveform-'+ID+' + p').remove()
				$('#waveform-'+ID).css('display','block')
			})
	
			wsInstance.on('finish', () => {
				$('#playPause-'+ID).children()[0].src = `${webURL}reckStatic/images/play.png`
			});
		}

		$(document).on("click",".playPause",function() {
			elementId = this.id;
			actualId = parseInt(this.getAttribute('data-id'));

        	const instance = AppState.wavesurfer.get(actualId);

			if(instance){
				if(!instance.isPlaying())
					$('#playPause-'+actualId).children()[0].src = `${webURL}reckStatic/images/pause.png`
				else 
					$('#playPause-'+actualId).children()[0].src = `${webURL}reckStatic/images/play.png`
			}
			instance.playPause()
		})

		//Remove loader class from elements before next round of elements come in because it is adding loader class two times because of that i think 
		//Add skeleton loader instaed of rounf
		function addLoader(){
			AppState.slides.forEach((item, index, array) => {
			//Do not add loader to last 'All done' page
			if(AppState.isLastPage === true && (index === array.length - 1)){
				console.log("last 11th record")
				return;
			}

			const loader = document.createElement( 'div');
			loader.className = 'loader';
			item.appendChild(loader);

			const media = item.querySelector('img, video, audio, p');
			if (media?.tagName === 'IMG') {
				media.onload = () => {
				item.classList.add('loaded');
				loader.remove();
				};
			} else if (media?.tagName === 'VIDEO' || media?.tagName === 'AUDIO') {
				media.onloadeddata = () => {
				item.classList.add('loaded');
				loader.remove();
				};
			}

			if(item.querySelector('loaded')){
				loader.remove();
				console.log("remove loader ",item)
			}
			});

		}
    });
</script>
</body>
</html>