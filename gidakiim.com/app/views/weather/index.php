<?php
	include_once APP_ROOT . '/views/header-footer/header.php';
?>

	<div class="container">
		<div class="background">
			<video autoplay muted loop>
				<source src='<?php echo URL_ROOT . '/media/videos/forest_rain.mp4'?>'></source>
			</video>
		</div> <!-- background-->
		<div id="graph" class='shadow'>
			<div id="controls">
				<div class="dropdown-container">
					<div class="dropdown-heading">
						<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="chart-line" class="svg-inline--fa fa-chart-line fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M496 384H64V80c0-8.84-7.16-16-16-16H16C7.16 64 0 71.16 0 80v336c0 17.67 14.33 32 32 32h464c8.84 0 16-7.16 16-16v-32c0-8.84-7.16-16-16-16zM464 96H345.94c-21.38 0-32.09 25.85-16.97 40.97l32.4 32.4L288 242.75l-73.37-73.37c-12.5-12.5-32.76-12.5-45.25 0l-68.69 68.69c-6.25 6.25-6.25 16.38 0 22.63l22.62 22.62c6.25 6.25 16.38 6.25 22.63 0L192 237.25l73.37 73.37c12.5 12.5 32.76 12.5 45.25 0l96-96 32.4 32.4c15.12 15.12 40.97 4.41 40.97-16.97V112c.01-8.84-7.15-16-15.99-16z"></path></svg>
						<h3>Data Select</h3>
					</div>
					<div class="dropdown">
						<div id="cities">
							<p class='ctrl-label'>Cities to graph</p>
							<div >
								<?php foreach($data['weatherData']['cities'] as $city): ?>
									<input type='checkbox' id='city' class='cb-toggle'<?php echo $city->id;?>' name='city<?php echo $city->id;?>' value='<?php echo $city->id?>'>
									<label for='city<?php echo $city->id;?>'> <?php echo $city->name?> </label>
									<br>
								<?php endforeach;?>
							</div>
						</div>

						<div id="dataParam">
							<p class='ctrl-label'>Parameter to graph</p>
							<select name="fileds" id="fields" class='select'>
								<option value="null">Select a field to graph</option>
								<?php foreach($data['weatherData']['fields'] as $field):?>
									<option value="<?php echo $field?>"><?php echo $field?></option>
								<?php endforeach;?>
							</select>
						</div>

						<div id="calendarStart">
							<p class='ctrl-label'>Start Date</p>
							<input type="date" name="startdate" id="startDate" value='<?php echo Date('Y-m-d', strtotime($data['weatherData']['dates'][0]))?>' 
							min='<?php echo Date('Y-m-d', strtotime($data['weatherData']['dates'][0]))?>' 
							max='<?php echo Date('Y-m-d', strtotime($data['weatherData']['dates'][1]))?>'>
						</div>

						<div id="calendarEnd">
							<p class='ctrl-label'>End Date</p>
							<input type="date" name="endDate" id="endDate" value='<?php echo Date('Y-m-d', strtotime($data['weatherData']['dates'][1]))?>' 
							min='<?php echo Date('Y-m-d', strtotime($data['weatherData']['dates'][0]))?>' 
							max='<?php echo Date('Y-m-d', strtotime($data['weatherData']['dates'][1]))?>'>
						</div>

						<div>
							<a href="<?php echo URL_ROOT?>/get_data" class="button" id="getData">Graph Selected Data</a>
						</div>

					</div>
				</div>

				<div class="dropdown-container">
					<div class="dropdown-heading">
						<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="cogs" class="svg-inline--fa fa-cogs fa-w-20" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path fill="currentColor" d="M512.1 191l-8.2 14.3c-3 5.3-9.4 7.5-15.1 5.4-11.8-4.4-22.6-10.7-32.1-18.6-4.6-3.8-5.8-10.5-2.8-15.7l8.2-14.3c-6.9-8-12.3-17.3-15.9-27.4h-16.5c-6 0-11.2-4.3-12.2-10.3-2-12-2.1-24.6 0-37.1 1-6 6.2-10.4 12.2-10.4h16.5c3.6-10.1 9-19.4 15.9-27.4l-8.2-14.3c-3-5.2-1.9-11.9 2.8-15.7 9.5-7.9 20.4-14.2 32.1-18.6 5.7-2.1 12.1.1 15.1 5.4l8.2 14.3c10.5-1.9 21.2-1.9 31.7 0L552 6.3c3-5.3 9.4-7.5 15.1-5.4 11.8 4.4 22.6 10.7 32.1 18.6 4.6 3.8 5.8 10.5 2.8 15.7l-8.2 14.3c6.9 8 12.3 17.3 15.9 27.4h16.5c6 0 11.2 4.3 12.2 10.3 2 12 2.1 24.6 0 37.1-1 6-6.2 10.4-12.2 10.4h-16.5c-3.6 10.1-9 19.4-15.9 27.4l8.2 14.3c3 5.2 1.9 11.9-2.8 15.7-9.5 7.9-20.4 14.2-32.1 18.6-5.7 2.1-12.1-.1-15.1-5.4l-8.2-14.3c-10.4 1.9-21.2 1.9-31.7 0zm-10.5-58.8c38.5 29.6 82.4-14.3 52.8-52.8-38.5-29.7-82.4 14.3-52.8 52.8zM386.3 286.1l33.7 16.8c10.1 5.8 14.5 18.1 10.5 29.1-8.9 24.2-26.4 46.4-42.6 65.8-7.4 8.9-20.2 11.1-30.3 5.3l-29.1-16.8c-16 13.7-34.6 24.6-54.9 31.7v33.6c0 11.6-8.3 21.6-19.7 23.6-24.6 4.2-50.4 4.4-75.9 0-11.5-2-20-11.9-20-23.6V418c-20.3-7.2-38.9-18-54.9-31.7L74 403c-10 5.8-22.9 3.6-30.3-5.3-16.2-19.4-33.3-41.6-42.2-65.7-4-10.9.4-23.2 10.5-29.1l33.3-16.8c-3.9-20.9-3.9-42.4 0-63.4L12 205.8c-10.1-5.8-14.6-18.1-10.5-29 8.9-24.2 26-46.4 42.2-65.8 7.4-8.9 20.2-11.1 30.3-5.3l29.1 16.8c16-13.7 34.6-24.6 54.9-31.7V57.1c0-11.5 8.2-21.5 19.6-23.5 24.6-4.2 50.5-4.4 76-.1 11.5 2 20 11.9 20 23.6v33.6c20.3 7.2 38.9 18 54.9 31.7l29.1-16.8c10-5.8 22.9-3.6 30.3 5.3 16.2 19.4 33.2 41.6 42.1 65.8 4 10.9.1 23.2-10 29.1l-33.7 16.8c3.9 21 3.9 42.5 0 63.5zm-117.6 21.1c59.2-77-28.7-164.9-105.7-105.7-59.2 77 28.7 164.9 105.7 105.7zm243.4 182.7l-8.2 14.3c-3 5.3-9.4 7.5-15.1 5.4-11.8-4.4-22.6-10.7-32.1-18.6-4.6-3.8-5.8-10.5-2.8-15.7l8.2-14.3c-6.9-8-12.3-17.3-15.9-27.4h-16.5c-6 0-11.2-4.3-12.2-10.3-2-12-2.1-24.6 0-37.1 1-6 6.2-10.4 12.2-10.4h16.5c3.6-10.1 9-19.4 15.9-27.4l-8.2-14.3c-3-5.2-1.9-11.9 2.8-15.7 9.5-7.9 20.4-14.2 32.1-18.6 5.7-2.1 12.1.1 15.1 5.4l8.2 14.3c10.5-1.9 21.2-1.9 31.7 0l8.2-14.3c3-5.3 9.4-7.5 15.1-5.4 11.8 4.4 22.6 10.7 32.1 18.6 4.6 3.8 5.8 10.5 2.8 15.7l-8.2 14.3c6.9 8 12.3 17.3 15.9 27.4h16.5c6 0 11.2 4.3 12.2 10.3 2 12 2.1 24.6 0 37.1-1 6-6.2 10.4-12.2 10.4h-16.5c-3.6 10.1-9 19.4-15.9 27.4l8.2 14.3c3 5.2 1.9 11.9-2.8 15.7-9.5 7.9-20.4 14.2-32.1 18.6-5.7 2.1-12.1-.1-15.1-5.4l-8.2-14.3c-10.4 1.9-21.2 1.9-31.7 0zM501.6 431c38.5 29.6 82.4-14.3 52.8-52.8-38.5-29.6-82.4 14.3-52.8 52.8z"></path></svg>
						<h3 >Controls</h3>
					</div>
					
					<div class="dropdown">
						<p>Zoom: Scroll Up / Down</p>
						<p>Pan: Left Click and Drag</p>
						<label for="showZero">Scatter Plot</label><input type="checkbox" name="chartType" id="chartType" class='cb-toggle'><label for="showZero">Line Chart</label><br>
						<input type="checkbox" name="fill" id="fill" class='cb-toggle'><label for="fill">Fill</label><br>
						<input type="checkbox" name="showZero" id="showZero" class='cb-toggle'><label for="showZero">Show Zeros</label><br>
						<input type="number" name="pointSize" id="pointSize" value=5 min=1 max=10><lable for='iconSize'>Datapoint Size</label><br>
						<div>
							<a href="#" class="button" id="resetGraph">Reset Graph</a>
						</div>
					</div>
				</div>

				<div class="dropdown-container">
					<div class="dropdown-heading">
						<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="file-download" class="svg-inline--fa fa-file-download fa-w-12" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="currentColor" d="M224 136V0H24C10.7 0 0 10.7 0 24v464c0 13.3 10.7 24 24 24h336c13.3 0 24-10.7 24-24V160H248c-13.2 0-24-10.8-24-24zm76.45 211.36l-96.42 95.7c-6.65 6.61-17.39 6.61-24.04 0l-96.42-95.7C73.42 337.29 80.54 320 94.82 320H160v-80c0-8.84 7.16-16 16-16h32c8.84 0 16 7.16 16 16v80h65.18c14.28 0 21.4 17.29 11.27 27.36zM377 105L279.1 7c-4.5-4.5-10.6-7-17-7H256v128h128v-6.1c0-6.3-2.5-12.4-7-16.9z"></path></svg>
						<h3>Download Data</h3>
					</div>
					<div class="dropdown">
						<div>
							<a href="<?php echo URL_ROOT?>/csv" class="button" id='csv' target="_blank" rel="noopener noreferrer" download="<?php echo date('Y-m-d')?>_weather_data">Download Current Data</a>
						</div>
						<div>
							<a href="<?php echo URL_ROOT?>/csvAll" class="button" id='csvAll' target="_blank" rel="noopener noreferrer" download="<?php echo date('Y-m-d')?>_weather_data">Download All Data</a>
						</div>
					</div>
				</div>

				<div class="dropdown-container">
					<div class="dropdown-heading">
						<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="database" class="svg-inline--fa fa-database fa-w-14" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M448 73.143v45.714C448 159.143 347.667 192 224 192S0 159.143 0 118.857V73.143C0 32.857 100.333 0 224 0s224 32.857 224 73.143zM448 176v102.857C448 319.143 347.667 352 224 352S0 319.143 0 278.857V176c48.125 33.143 136.208 48.572 224 48.572S399.874 209.143 448 176zm0 160v102.857C448 479.143 347.667 512 224 512S0 479.143 0 438.857V336c48.125 33.143 136.208 48.572 224 48.572S399.874 369.143 448 336z"></path></svg>
						<h3>Manual Data Collection</h3>
					</div>
					<div class="dropdown">
						<div>
							<a href="<?php echo URL_ROOT?>/scrape_data" class="button" id='manualDataUpdate' target="_blank" rel="noopener noreferrer" >Manually Scrape Data</a>
						</div>
					</div>
				</div>
					
			</div><!-- #controls-->
			<div id="graphArea">
				<div id="header">
					<h2>Bemidji Vs Pressure</h2>
					<div><p>Series:</p></div>
					
				</div> <!-- #header -->
				<div id="plotArea">
					<canvas id="canvas"></canvas>
				</div> <!-- #plotArea -->

			</div><!-- #graphArea -->
		</div><!-- #graph-->

	</div> <!-- container -->
		
			
			
	
<?php
include_once APP_ROOT . '/views/header-footer/footer.php';