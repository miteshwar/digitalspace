<?php
session_start();


// Check if the dealer ID is set in the session
if (!isset($_SESSION['dealer_id'])) {
    header("Location: index.php");  // Redirect to login if not logged in
    exit();
}

// Embed dealer ID in the JavaScript
$dealer_id = $_SESSION['dealer_id'];
?>

<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Quba Spin Wheel</title>
	
	<!--<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">-->
	
    <link rel="icon" href="favicon.webp" type="image/x-icon">

    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css?v=28">
	
	
    <!--<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.min.js"></script>-->
    <!--<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>-->
    <!--<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>-->
    <!--<script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-datalabels/2.1.0/chartjs-plugin-datalabels.min.js"></script> -->
    
    <script src="js/confetti.browser.min.js"></script>
    <script src="js/chart.min.js"></script>
    <script src="js/chartjs-plugin-datalabels.min.js"></script> 
    
    
    <script>
      // Retrieve dealerId from PHP session and set it in JavaScript
      const dealerId = <?php echo $_SESSION['dealer_id']; ?>;
      console.log(dealerId); // This should log 2
    </script>

	
</head>
<body>
    
	<div class="container">
		<div class="row ">
			<!-- Logo Column -->
			 <div class="col-sm-4 col-12 mb-3">
			    <div class="logo">
				<img src="asset/quba_logo_black.svg" alt="Quba Logo" >
				</div>
			</div>
		
			<!-- Countdown Timer Column -->
			 <div class="col-sm-4 col-12 mb-3">
			    
				<div id="timer" style=""></div>
			</div>
		
			<div class="col-sm-4 col-12">
				
			</div>
		</div>


		
	<div class="row wheel_row">
			<!-- Logo Column -->
			<div class="col-lg-4 col-sm-2 " >
			
			</div>

			 <div class="col-lg-4  col-sm-8">              
				 <div class="wheel_box">
				  <canvas id="spinWheel"></canvas>
				  <button id="spin_btn">Spin</button>
				  <!--<i class="fa-solid fa-location-arrow"></i>-->
				  <img src="asset\arrow.svg" class="fa-solid" alt="" width="30px" height="30px">

				 </div> 

  

				<div id="prizeModal" class="modal">
				  <div class="modal-content">
					<div class="prize-container box">
					  <img id="prizeImage" src="" alt="Prize Image" />
					  <span id="congratulations" class="congratulations">Congratulations on win!</span>
					  <span id="prizeMessage" class="prizeMessage">You won a [prize].</span>
					</div>
					<span id="closeModal" class="close-icon">&times;</span> 
				  </div>
				</div>
	
				<canvas id="confettiCanvas" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 1;">
				</canvas>
			</div>
			
			 <div class="col-lg-4  col-sm-2">
			 
			</div>
		</div>
		



	<div class="floating-div-right">
	    <center><div id="event_name" class="box_event "></div>
	    <div class="floating-div-right-container">
	        
            <div class="dealer-info row">
                <div class="col-lg-3  col-sm-3">
                <img id="dealer-image" src="" alt="Dealer Image" class="dealer-image" />    
                </div>
                <div class="col-lg-9  col-sm-9">
                    <p id="dealer-name" class="dealer-name"></p>
                    <p id="dealer-zone" class="dealer-zone"></p>
                </div>
            </div>
            <div class="dealer-details">
                <div class="box_spin row">
                    <div class="col-lg-6  col-sm-6 box_right_line">
                        <p class="spin-title">Total Spins</p>
                        <span id="dealer-spins"  class="spin-counts">0</span>
                    </div>
                    <div class="col-lg-6  col-sm-6">
                        <p  class="spin-title">No of Attempts</p>
                        <span id="dealer-attempts" class="spin-counts">0</span>
                    </div>
                </div>
                <div class=" row">
                    <div class="col-lg-12  col-sm-12">
                        <h2 id="spin-left" class="spin-left">Wheel Spins Left: <span>0</span> </h2>
                    </div>
                </div>
            </div>
            <hr class="hr_line"></hr>
            
            <div class="prize-info row" style="display: block;" id="prize-won">
                <div class="col-lg-3  col-sm-3">
                    <img id="prize-image" src="" alt="Prize Image" class="prize-image" />    
                </div>
                <div class="col-lg-9  col-sm-9">
                    <p id="prize-name" class="prize-name"><span></span></p>
                    <p id="prize-congratulations" class="prize-congratulations">Congratulations on win!</p>
                </div>
            </div>
            
          
        </div>
            
            </center>
</div>

    </br>


  

  
</div>

<script src="script.js?v=27"></script>

</body>   
</html>