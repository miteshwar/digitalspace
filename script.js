let globalEventId = 0;
let globalZoneId = 0;
let noOfCoupons = 0; // Global variable for number of coupons
let sections = []; 

let spinButtonClickListener;
let ctx;
let canvas;
let drawWheel;
let spintTypeId=3;
const PRIZE_NAME = "Sorry";

let prizeName1=PRIZE_NAME;


let prizeWon="";
const BASE_URL = "https://rewards.qubaindia.com/";
const BASE_URL_ADMIN = "https://rewards.thinkogic.com/";


canvas = document.getElementById("spinWheel");

spinButtonClickListener = () => {
 	console.log("Button Clicked");
 	 	const spinButton = document.getElementById("spin_btn");
 	 	console.log("Spins Click : Disabled.");
    spinButton.disabled = true; // Disable the button during spinning
    spinButton.removeEventListener("click", spinButtonClickListener);
 	
 	let noOfSpins=0;
 	
 	if(spintTypeId==2)
 	{
 	 fetch(`${BASE_URL}get_random_spindata.php?dealerid=${dealerId}&eventId=${globalEventId}&zoneId=${globalZoneId}`)
        .then(response => response.json())  // Parse JSON response
        .then(data => {
            if (data.status === 'success') {
                console.log('Prize Name:', data.data.PrizeName);
                prizeName1 = data.data.PrizeName;
                const noOfSpins =sections.length;
                
                createSpinWheel(noOfSpins, sections, prizeName1);
                console.log('Spin 1:', prizeName1);
                 startSpin(prizeName1,0);
            } else if (data.message === 'Invalid user') {
                // Handle invalid user case
                alert('You are not logged in. Redirecting to the home page.');
                window.location.href = BASE_URL; // Redirect to BASE_URL
            } else {
                // Handle error: prize not found
                prizeName1 = PRIZE_NAME;
                console.log('Spin 2:', prizeName1);
                startSpin(prizeName1,1);
                console.log('Error:', data.message);
            }
        })
        .catch(error => {
            // Handle any errors with the request
            console.error('Error making request:', error);
        });
 	}
 	else
 	{
 	    if(spintTypeId==3){
     	    console.log('Spin 4:', prizeName1);
     	    startSpin(prizeName1,0);    
 	    }
 	    else
 	    {
     	    console.log('Spin 3:', prizeName1);
     	    startSpin(prizeName1,1);
 	    }
 	}
       
 	
  };
  document.getElementById("spin_btn").addEventListener("click", spinButtonClickListener);
 
  


document.addEventListener('DOMContentLoaded', function() {
    fetchAndUpdateData(); // Fetch and update data when the page loads
});

// Function to reset the prize details
function resetPrizeDetails() {
	// Reset the prize message to default
	document.getElementById('prize-won').style.display = 'none'; // Hide prize section
	document.getElementById('prize-image').src = '';  // Reset prize image
}


function fetchAndUpdateData() {




fetch(`${BASE_URL}get_dealer_spindata.php?dealerid=${dealerId}`) 
  .then(response => response.json())
  .then(json => {
    if (json.status === "success" && json.data) {
		const eventEndDate = json.data.EventEndDate; // Get EventEndDate
		const eventEndTime = json.data.EventEndTime; // Get EventEndTime
		
		
		globalEventId= json.data.EventId;
		globalZoneId= json.data.ZoneId;
		spintTypeId= json.data.SpinTypeId;
	
		
		const eventName = json.data.EventName; 
		const dealerPhoto = json.data.DealerPhoto;
		const dealerName = json.data.DealerName; 
		const zoneName = json.data.ZoneName; 
		 
		const playedSpinAttempts = json.data.PlayedSpinAttempts; 	
		const attemptNo1 = json.data.AttemptNo1; 	
		const defaultAllocated = json.data.DefaultAllocated; 	
		prizeWon = json.data.PrizeName1; 
		
		if(defaultAllocated ==1)
		{
		    prizeName1 = json.data.PrizeName1; 
		}
		else
		{
		    prizeName1 = PRIZE_NAME;    
		}
		
		
		const prizePhoto1 = json.data.PrizePhoto1; 
		const winCount = json.data.WinCount; 
		
		noOfCoupons = json.data.NoOfCoupons; 
		sections = json.sections.map(section => ({
          label: section.label, 
          desc: section.desc,
          image: section.image
        }));

        // Extract event details
        const eventStatus = json.data.EventStatus; // Status of the event
        const eventStartDateTime = `${json.data.EventStartDate}T${json.data.EventStartTime}`; // Combine start date and time
        const eventEndDateTime = `${json.data.EventEndDate}T${json.data.EventEndTime}`; // Combine end date and time
        
        
        // Call initializeCountdown with the extracted parameters
        initializeCountdown(eventStatus, eventStartDateTime, eventEndDateTime);
        
        
		const spinButton = document.getElementById("spin_btn");
		console.log("Spins get data : Enabled.");
		spinButton.disabled = false;		
		
		if (playedSpinAttempts < noOfCoupons) {
			console.log("Spins allowed.");
			spinButton.disabled = false;
			document.getElementById("spin-left").querySelector("span").textContent = noOfCoupons-playedSpinAttempts ;
			
			updateSpinButton(eventStatus, eventStartDateTime, eventEndDateTime);
		} else {
			spinButton.disabled = true;
			console.log("No more spins allowed.");
			document.getElementById('spin-left').textContent = "Sorry, No Spins Left!";
		}
		
        document.getElementById("event_name").innerHTML =  eventName;
		document.getElementById("dealer-image").src = BASE_URL_ADMIN + dealerPhoto;
		document.getElementById("dealer-name").textContent = dealerName;
		
		document.getElementById("dealer-zone").innerHTML = zoneName;
		document.getElementById("dealer-spins").innerHTML = noOfCoupons;
		document.getElementById("dealer-attempts").innerHTML = playedSpinAttempts;
		
		

		if (winCount>0) {
			// Update the prize message
// 			document.getElementById('prize-win-message').innerText = `Congratulations!`;
// 			document.getElementById('prize-win-message').style.color = '#2e8b57';  // Change to green on winning
			
			// Show the prize section with a fade-in effect
			document.getElementById('prize-won').style.display = 'flex';
			document.getElementById('prize-image').src =BASE_URL_ADMIN +  prizePhoto1; // Replace with actual image path
		//	document.getElementById("prize-name").querySelector("span").textContent= prizeWon; // Replace with actual image path
			
			document.getElementById("prize-name").textContent = prizeWon;
			

		} else {
			// Show the 'no prize' message
// 			document.getElementById('prize-win-message').innerText = "Sorry No Prize yet!";
// 			document.getElementById('prize-win-message').style.color = '#ff6347';  // Default red color
			document.getElementById('prize-won').style.display = 'none';
		}
		

		// Combine date and time into a single string
		const targetDateTime = `${eventEndDate}T${eventEndTime}`;
		const targetDate = new Date(targetDateTime).getTime(); // Convert to timestamp
		
		// Start the countdown
// 		startCountdown(targetDate);

        
        
        		
      
		const noOfSpins =sections.length;
		// Create the spin wheel with sections and the fixed prize
		
    		if(attemptNo1 == playedSpinAttempts+1)
    		{
    			createSpinWheel(noOfSpins, sections, prizeName1);
    		}
    		else
    		{
    		    prizeName1 = PRIZE_NAME;
    			createSpinWheel(noOfSpins, sections,prizeName1);
    		}
		
		
    } else {
		document.getElementById("timer").innerHTML = "Dealer Not Found!";
    }
  })
  .catch(error => {
		console.error("Error fetching event details:", error);
		document.getElementById("timer").innerHTML = "Starting...";
  });


}

function updateSpinButton(eventStatus, eventStartDateTime, eventEndDateTime) {
  const spinButton = document.getElementById("spin_btn");

  // Convert event start and end times to timestamps
  const startTime = new Date(eventStartDateTime).getTime();
  const endTime = new Date(eventEndDateTime).getTime();
  const now = new Date().getTime();

  if (eventStatus === 1 && now >= startTime && now <= endTime) {
    // Event is started and within valid time
    console.log("Spins Update: Enabled during valid event time.");
    spinButton.disabled = false; // Enable the button
    spinButton.addEventListener("click", spinButtonClickListener);
  } else {
    // Event is not started, completed, cancelled, or outside valid time
    console.log("Spins Update: Disabled.");
    spinButton.disabled = true; // Disable the button
    spinButton.removeEventListener("click", spinButtonClickListener);
  }
}



function initializeCountdown(eventStatus, eventStartDateTime, eventEndDateTime) {
  const startDate = new Date(eventStartDateTime).getTime();
  const endDate = new Date(eventEndDateTime).getTime();
  const now = new Date().getTime();

  if (now < startDate) {
    // Event not started yet
    startCountdown(startDate, "Event Starts in", "Event Started!");
    
  } else if (now >= startDate && now <= endDate) {
    // Event started but not yet ended
    startCountdown(endDate, "Event Ends in", "Event Ended!");
   
  } else {
    // Event has already ended
    document.getElementById("timer").innerHTML = "Event Ended!";
  }
}

function startCountdown(targetDate, runningText, endText) {
  const countdown = setInterval(() => {
    const now = new Date().getTime();
    const timeLeft = targetDate - now;

    if (timeLeft > 0) {
      const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
      const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

      document.getElementById("timer").innerHTML = 
        `${runningText} ${days}d ${hours}h ${minutes}m ${seconds}s`;
    } else {
      clearInterval(countdown);
      document.getElementById("timer").innerHTML = endText;
      
if (endText === "Event Started!") {
  setTimeout(() => {
    location.reload(); // This will refresh the page
  }, 1000); // Adjust the delay (1000ms = 1 second) if needed
}
      
      
    }
  }, 1000);
}


// function startCountdown(targetDate) {
// 	// Update the countdown every second
// 	const countdown = setInterval(() => {
// 	const now = new Date().getTime();
// 	const timeLeft = targetDate - now;
	
// 	// Calculate days, hours, minutes, and seconds
// 	const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
// 	const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
// 	const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
// 	const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

//     // Display the result in the timer div
//     document.getElementById("timer").innerHTML = "Event Ends in " +
//       `${days}d ${hours}h ${minutes}m ${seconds}s`;

//     // If the countdown is over, display a message
//     if (timeLeft < 0) {
//       clearInterval(countdown);
//       document.getElementById("timer").innerHTML = "Event Ended!";
//     }
//   }, 1000);
// }

 

function createSpinWheel(noOfSections, sections, fixedPrize) {
    console.log('Create Wheel');
	if (noOfSections !== sections.length) {
		console.error("Number of sections must match the length of the sections array.");
		return;
	}
	
	if (!sections.some((section) => section.label === fixedPrize)) {
		console.error("Fixed prize must be present in the sections array.");
		return;
	}

// 	canvas = document.getElementById("spinWheel");
	canvas.width = 600;
	canvas.height = 600;
	ctx = canvas.getContext("2d");
	ctx.imageSmoothingEnabled = true;
	
	const size = canvas.width;
	const sectionAngle = 360 / noOfSections;
	const fixedPrizeIndex = sections.findIndex((section) => section.label === fixedPrize);

  


drawWheel = () => {
    console.log('Draw Wheel');
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    sections.forEach((section, index) => {
        const startAngle = (index * sectionAngle * Math.PI) / 180;
        const endAngle = ((index + 1) * sectionAngle * Math.PI) / 180;

        // Define gradient for the current section
        const gradient = ctx.createLinearGradient(0, 0, size, 0);
        gradient.addColorStop(0, index % 2 === 0 ? "#000000" : "#852405");
        gradient.addColorStop(1, index % 2 === 0 ? "#F2B01C" : "#F17022");

        // Draw the section
        ctx.beginPath();
        ctx.moveTo(size / 2, size / 2);
        ctx.arc(size / 2, size / 2, size / 2, startAngle, endAngle);
        ctx.fillStyle = gradient;
        ctx.fill();
        ctx.stroke();

        // Add image (if present) closer to the edge
        if (section.imageElement) {
            const angle = startAngle + (endAngle - startAngle) / 2;
            const x = size / 2 + Math.cos(angle) * (size / 4);
            const y = size / 2 + Math.sin(angle) * (size / 4);
            const imgSize = 85;
            ctx.save();
            ctx.translate(x, y);
            ctx.rotate(angle + Math.PI / 2);
            ctx.drawImage(section.imageElement, -imgSize / 2, -imgSize / 2, imgSize, imgSize);
            ctx.restore();
        }

        // Add text, farther from the image to avoid overlap
        ctx.save();
        ctx.translate(size / 2, size / 2);
        const angle = (startAngle + endAngle) / 2;
        ctx.rotate(angle); // Align text with section
        ctx.translate(size / 2.6, 0); // Increase the radial distance to move text away from the image
        ctx.rotate(Math.PI / 2); // Rotate the text 90 degrees from its current orientation
        ctx.textAlign = "center";
        ctx.fillStyle = "#fff";
        ctx.font = "bold 24px Arial";
        ctx.fillText(section.desc, 0, 0); // Draw at the adjusted position
        ctx.restore();
    });
};




  let loadedImages = 0;
  sections.forEach((section) => {
    const img = new Image();
    img.src =BASE_URL_ADMIN +  section.image;
    section.imageElement = img;
    img.onload = () => {
      loadedImages++;
      if (loadedImages === sections.length) {
        drawWheel(); // Draw wheel only after all images are loaded
      }
    };
    img.onerror = () => {
      console.error(`Failed to load image for section: ${section.label}`);
    };
  });

 
  
 
 
}


function startSpin(fixedPrize,addSpincount){
     const spinButton = document.getElementById("spin_btn");
     console.log("Spin Start : Disabled.");
    spinButton.disabled = true; // Disable the button during spinning
    spinButton.removeEventListener("click", spinButtonClickListener);
    
    updateAttempts(addSpincount);
    
    const duration = 5000;
    const fps = 60;
    const totalFrames = (duration / 1000) * fps;

    let currentAngle = 0;
    let currentFrame = 0;

    const noOfSections =sections.length;
    const sectionAngle = 360 / noOfSections;
    const fixedPrizeIndex = sections.findIndex((section) => section.label === fixedPrize);

    const fixedPrizeAngle =
      360 - (fixedPrizeIndex * sectionAngle + sectionAngle / 2);
    const extraSpins = 3 * 360;
    const targetAngle = extraSpins + fixedPrizeAngle;
    const size = canvas.width;

	const spinSound = new Audio("sounds/wheel_spin.mp3"); // Path to your sound file
  	spinSound.loop = false; // Loop the sound during the spin
  	spinSound.play();
  
    const spin = () => {
      if (currentFrame < totalFrames) {
        const progress = currentFrame / totalFrames;
        const easing = 1 - Math.pow(1 - progress, 3);
        currentAngle = easing * targetAngle;

        ctx.clearRect(0, 0, size, size);
        ctx.save();
        ctx.translate(size / 2, size / 2);
        ctx.rotate((currentAngle * Math.PI) / 180);
        ctx.translate(-size / 2, -size / 2);
        drawWheel();
        ctx.restore();

        currentFrame++;
        requestAnimationFrame(spin);
      } else {
       	spinSound.pause();
      	spinSound.currentTime = 0;
      	
      	console.log("Fized Prize:", fixedPrize);

      
        const prizeDetails = sections.find((section) => section.label === fixedPrize);
        console.log(" Prize Details:", prizeDetails);
        if (prizeDetails) {
		 console.log("Prize won:", fixedPrize);
         showPrizeWonMessage(fixedPrize, prizeDetails.image);
        
           
        document.getElementById("spin_btn").addEventListener("click", spinButtonClickListener);
        	
          
        } else {
          console.error("Prize details not found!");
        }
        
       
      }
    };

    spin();
}


function updateAttempts(addSpincount){
console.log("Inside PlayedSpinAttempts");
			
			
			fetch(`${BASE_URL}update_dealer_spin_data.php?dealerid=${dealerId}&addSpincount=${addSpincount}`, {
   
			
				method: 'GET',
			})
			.then(response => response.json())
			.then(data => {
				if (data.status === "success") {
					console.log("PlayedSpinAttempts updated successfully.");
				} else {
					console.error("Error:", data.message);
				}
			})
			.catch(error => {
				console.error("Error calling PHP script:", error);
			});
}

// Function to show the prize modal
function showPrizeWonMessage(prize, image) {
	const modal = document.getElementById("prizeModal");
	const prizeMessage = document.getElementById("prizeMessage");
	const prizeImage = document.getElementById("prizeImage");
	const congratulations = document.getElementById("congratulations");
	
	
	

	
	prizeImage.src = BASE_URL_ADMIN + image;
	if(prize !=PRIZE_NAME)
	{
// 		congratulations.style.display = "block";
		congratulations.textContent = "Congratulations on win!";
		launchConfetti();
		prizeMessage.textContent = `You won a ${prize}!`;
		
		const spinWin = new Audio("sounds/winning_sound.mp3"); // Path to your sound file
      	spinWin.loop = false; // Loop the sound during the spin
      	spinWin.play();
	}
	else
	{
		congratulations.textContent = "No Prize!";
		prizeMessage.textContent = "Spin Once Again";
		const spinWin = new Audio("sounds/noprize_sound.mp3"); // Path to your sound file
      	spinWin.loop = false; // Loop the sound during the spin
      	spinWin.play();
	}





  // Show the modal
  modal.style.display = "flex"; // Make modal visible

  // Close the modal when the user clicks the close button
  const closeBtn = document.getElementById("closeModal");
  closeBtn.onclick = () => {  
	modal.style.display = "none"
	fetchAndUpdateData();
	resetPrizeDetails();
	
  };

  // Close the modal when clicking outside of it
  window.onclick = (event) => {
  };
}

function launchConfetti() {
  const duration = 5 * 1000; // 5 seconds
  const end = Date.now() + duration;

  const canvas = document.getElementById('confettiCanvas');  // Get the predefined canvas element

  (function frame() {
    confetti({
      particleCount: 5,
      angle: 60,
      spread: 55,
      origin: { x: 0 },
    });
    confetti({
      particleCount: 5,
      angle: 120,
      spread: 55,
      origin: { x: 1 },
    });

    if (Date.now() < end) {
      requestAnimationFrame(frame);
    }
  })();
}


