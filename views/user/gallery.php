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
	<script src="<?php echo $webURL;?>reckStatic/js/common.js"  crossorigin="anonymous"></script>
	<script src="<?php echo $webURL;?>reckStatic/js/UI-user.js"  crossorigin="anonymous"></script>
	<script>
		const slides = document.querySelectorAll('.media-slide');
		let current = 0;
	
		function showNextSlide(){
			if (current < slides.length - 1) {
				console.log("inside",slides[current], slides[current+1])
				slides[current].classList.remove('active');
				current++;
				slides[current].classList.add('active');
			  } else {
				// End of media
				// document.getElementById('nextBtn').disabled = true;
				console.log("You've reached the end of the exhibition!");
			  }
		}

		// Swipe up detection (basic touch event)
		let startY = 0;
		let isScrolling = false;
		let isPinching = false;

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
				}, 800);
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
			}, 800);
			
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
        $(document).ready(async function () {
            const mediaList = document.getElementById("media-list");
            mediaList.innerHTML = "";
            let result = '';
            await listGalleryMediaFiles().then((result)=>{
            if(result?.total == 0){
                let div = document.createElement("div");
                div.innerHTML = '<div>Files are currenly unavailable!</div>';
                mediaList.append(div);
            }else{
                let mediaFiles = result.data; 
                console.log('media ',mediaFiles);
            }
            });
        });
	  </script>
</body>
</html>