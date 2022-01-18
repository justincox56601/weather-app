/**
 * graphing library for the app
 * Graph is the main class that interacts with the user and the screen
 * DataObject converts raw data into Series info, and dataPoints
 * DataPoint stores the original data, as well as the converted X, Y coordinates 
 */

class Graph{
	constructor(canvas){
		this.canvas = canvas;
		this.ctx = this.canvas.getContext('2d');
		this.data = null;
		this.canvasSettings();

		this.settings = {
			padding: 0.1,
			x: 0,
			y: 0,
			scale: 1,
			scaleMin: 0.125,
			scaleMax: 4,
			mouseDown : false,
			mouseOver: false,
			delta : {x:0, y:0},
			
		};

		this.settings.graph = {
			width: this.canvas.width * 0.8, //graph is 80% of the width of the canvas
			height: this.canvas.height * 0.8, // graph is 80% of the height of the canvas
			x: this.canvas.width * 0.1, //x origin
			y: this.canvas.height * 0.9, // y origin
			radius: 5,
			fill: false,
			showZero: false,
			lineChart : false,
		};

		this.settings.listeners = [
			{event: 'wheel', callback: 'zoom'},
			{event: 'mousedown', callback: 'mouseDown'},
			{event: 'mouseup', callback: 'mouseUp'},
			{event: 'mouseover', callback: 'mouseOver'},
			{event: 'mousemove', callback: 'mouseMove'},
			{event: 'mouseout', callback: 'mouseOut'},
		];

		this.controls = [
			{item: '#getData', callback: 'getData', event: 'click'},
			{item: '#resetGraph', callback: 'resetGraph', event: 'click'},
			{item: '#fields', callback: 'updateFields', event: 'click'},
			{item: '#fill', callback: 'fill', event: 'click'},
			{item: '#showZero', callback: 'showZero', event: 'click'},
			{item: '#startDate', callback: 'startDate', event: 'change'},
			{item: '#endDate', callback: 'endDate', event: 'change'},
			{item: '#pointSize', callback: 'pointSize', event: 'change'},
			{item: '#csv', callback: 'fetchCsv', event: 'click'},
			{item: '#csvAll', callback: 'fetchCsvAll', event: 'click'},
			{item: '#manualDataUpdate', callback: 'manualDataUpdate', event: 'click'},
			{item: '#chartType', callback: 'chartType', event: 'click'}
		];

		//special control listener for the cities option
		let cities = document.querySelectorAll('[id^=city]');
		cities.forEach(city=>{
			city.addEventListener('click', e=>{
				if(city.checked){ //if checked add the city value to the array if it isnt already there
					if(! this.params.city.includes(parseInt(city.value))){
						this.params.city.push(parseInt(city.value));
					}
				}else{ // if unchecked, remove the city value if it is there.
					if(this.params.city.includes(parseInt(city.value))){
						const index = this.params.city.indexOf(parseInt(city.value));
						if(index > -1){
							this.params.city.splice(index, 1);
						}
						
					}
				}
			})
		});

		this.params = {
			ajax: '',
			city: [],
			column: '',
			start: '',
			end: '',
		}
		
		this.addListeners();
		this.addControls();

		
		this.draw();
	}

	canvasSettings(){
		this.canvas.style.width = '100%';
		this.canvas.style.height = '100%';
		this.canvas.width = this.canvas.offsetWidth;
		this.canvas.height = this.canvas.offsetHeight;
		this.canvas.style.border = '1px solid black';
		
	}

	/**
	 * ==================================
	 * 		Listeners
	 * ==================================
	 */

	addListeners(){
		this.settings.listeners.forEach(l=>{
			this.canvas.addEventListener(l.event, e=>{
				this[l.callback](e);
			});
		});
	}

	zoom(e){
		const s = e.deltaY * -0.001;
		this.settings.scale = Math.min(Math.max(this.settings.scale +s, this.settings.scaleMin), this.settings.scaleMax)
	}

	mouseDown(e){
		this.settings.mouseDown = true;
	}

	mouseUp(e){
		this.settings.mouseDown = false;
	}

	mouseMove(e){
		if(this.settings.mouseOver){
			const rect = this.canvas.getBoundingClientRect();
			this.mouseX = (e.clientX - rect.left) / this.settings.scale;
			this.mouseY = (e.clientY - rect.top) / this.settings.scale;

			if(this.settings.mouseDown){
				this.settings.delta.x += e.movementX;
				this.settings.delta.y += e.movementY;
			}
		}
	}

	mouseOver(e){
		this.settings.mouseOver = true;
	}

	mouseOut(e){
		this.settings.mouseOver = false;
	}

	/**
	 * ==================================
	 * 		Controls
	 * ==================================
	 */

	addControls(){
		this.controls.forEach(c=>{
			var control = document.querySelector(c.item);
			control.addEventListener(c.event, e=>{
				this[c.callback](e, control);
			});
		});
	}

	getData(e, control){
		e.preventDefault();
		const url = control.href;
		this.params.ajax = 'get_raw_data';
		let params = new URLSearchParams(this.params).toString();

		fetch(url + '?' + params).then(response=>response.json()).then(data=>this.filterData(data));
	}


	resetGraph(e, control){
		e.preventDefault();
		this.settings.scale = 1;
		this.settings.delta.x = 0;
		this.settings.delta.y = 0;
		this.settings.graph.radius = 5;
		document.querySelector('#pointSize').value = 5;
	}

	updateFields(e, control){
		this.params.column = control.value;
	}

	fill(e, control){
		this.settings.graph.fill = control.checked;
	}

	showZero(e, control){
		this.settings.graph.showZero = control.checked;
	}

	startDate(e, control){
		this.params.start = control.value;
	}

	endDate(e, control){
		this.params.end = control.value;
	}

	pointSize(e, control){
		this.settings.graph.radius = control.value;
	}

	fetchCsv(e, control){
		e.preventDefault();
		const url = control.href;
		this.params.ajax = 'get_csv';
		let params = new URLSearchParams(this.params).toString();

		fetch(url + '?' + params).then(response=>response.json()).then(data=>{window.location = data.url;});
	}

	fetchCsvAll(e, control){
		e.preventDefault();
		const url = control.href;
		this.params.ajax = 'get_csv_all';
		let params = new URLSearchParams(this.params).toString();
		fetch(url + '?' + params).then(response=>response.json()).then(data=>{window.location = data.url;});
	}

	manualDataUpdate(e, control){
		e.preventDefault();
		const url = control.href;
		this.params.ajax = 'get_scraped_data';
		let params = new URLSearchParams(this.params).toString();
		fetch(url + '?' + params).then(response=>response.json()).then(data=>{
			control.innerText = data.message;
			control.classList.add('success');
			window.setTimeout(()=>{
				control.innerText = 'Manually Scrape Data';
				control.classList.remove('success');
			}, 5000);
		});
	}

	chartType(e, control){
		this.settings.graph.lineChart = control.checked;
	}

	/**
	 * ==================================
	 * 		Data
	 * ==================================
	 */


	filterData(data){
		this.data = data;
		const DO = new DataObject(this.data, 'date', this.params.column, this.settings.graph.width, this.settings.graph.height); 
		this.data = DO.makeDataSet();

		//after filtering the new data, update the headers with the series info
		const header = document.querySelector('#header');
		const h2 = header.querySelector('h2');
		const p = header.querySelector('p');
		h2.innerText = this.data.graph.x.toUpperCase() + ' vs. ' + this.data.graph.y.toUpperCase();
		p.innerHTML = '';
		for(const [key, val] of Object.entries(this.data.series)){
			p.innerHTML += `<span style='color:${val.color}'>${val.name}</span>&emsp;`;
		};
		
	}

	/**
	 * ==================================
	 * 		Draw
	 * ==================================
	 */

	draw(){
		this.ctx.clearRect(0,0, this.canvas.width, this.canvas.height);
		this.drawCanvasLines();
		if(this.settings.graph.lineChart){
			this.lineData();
		}else{
			this.plotData();
		}
		
		

		this.canvas.style.transform = `translate3d(${this.settings.delta.x}px, ${this.settings.delta.y}px, 0px) scale(${this.settings.scale})`;

		window.requestAnimationFrame(()=>this.draw());
	}

	drawCanvasLines(){
		this.drawXAxis();
		this.drawYAxis();
		this.drawYLines();
		
	}

	drawXAxis(){
		this.ctx.lineWidth = 3;
		this.ctx.strokeStyle = 'black';
		this.ctx.beginPath();
		this.ctx.moveTo(this.settings.graph.x, this.settings.graph.y);
		this.ctx.lineTo(this.settings.graph.x + this.settings.graph.width, this.settings.graph.y);
		this.ctx.stroke();
	}

	drawYAxis(){
		this.ctx.lineWidth = 3;
		this.ctx.strokeStyle = 'black';
		this.ctx.beginPath();
		this.ctx.moveTo(this.settings.graph.x, this.settings.graph.y);
		this.ctx.lineTo(this.settings.graph.x, this.settings.graph.y - this.settings.graph.height);
		this.ctx.stroke();
	}

	drawYLines(){
		const percents = [0.25, 0.50, 0.75, 1.0];
		const left = this.canvas.width * this.settings.padding;
		const right = this.canvas.width * (1 - this.settings.padding);
		this.ctx.lineWidth = 1;
		this.ctx.strokeStyle = 'grey';
		
		percents.forEach(p=>{
			this.ctx.beginPath();
			this.ctx.moveTo(this.settings.graph.x, this.settings.graph.y - (this.settings.graph.height * p));
			this.ctx.lineTo(this.settings.graph.x + this.settings.graph.width, this.settings.graph.y - (this.settings.graph.height * p));
			this.ctx.stroke();
		});
	}

	plotData(){
		if(this.data != null){
			this.data.points.forEach(d=>{
				if(d.y == 0 && this.settings.graph.showZero == false){return}
				this.ctx.fillStyle = 'black';
				const xx = this.settings.graph.x + d.x;
				const yy = this.settings.graph.y - d.y;
				if(this.collisionDetection(xx, yy)){
					this.ctx.font = '16px serif';
					this.ctx.fillStyle = 'black';
					this.ctx.fillText(d.label, this.mouseX + this.settings.graph.radius, this.mouseY - this.settings.graph.radius);
				}
				this.drawShape(xx, yy, this.settings.graph.radius, d.shape, d.color, this.settings.graph.fill);
			});
		}
		
	}

	lineData(){
		if(this.data != null){
			let i, x1, y1, x2, y2, xx, yy, series;
			series = {}
			for(const [key, val] of Object.entries(this.data.series)){
				series[key] = {x:0, y:0};
			}
			
			
			for(i=1; i<this.data.points.length; i++){
				this.ctx.fillStyle = 'black';
				
				x1 = this.settings.graph.x + series[this.data.points[i].series].x;
				y1 = this.settings.graph.y - series[this.data.points[i].series].y;
				x2 = this.settings.graph.x + this.data.points[i].x;
				y2 = this.settings.graph.y - this.data.points[i].y;
				if(this.collisionDetection(x2, y2)){
					this.ctx.font = '16px serif';
					this.ctx.fillStyle = 'black';
					this.ctx.fillText(this.data.points[i].label, this.mouseX + this.settings.graph.radius, this.mouseY - this.settings.graph.radius);
				}
				this.drawLine(x1, y1, x2, y2,this.data.points[i].color);

				//check for and update current series
				series[this.data.points[i].series].x = this.data.points[i].x;
				series[this.data.points[i].series].y = this.data.points[i].y;
			}
		}
	}

	

	collisionDetection(x, y){
		//returns true if the distance between the mouseXY and the 
		//supplied XY is less than the radius
		const xx = this.mouseX - x;
		const yy = this.mouseY - y;
		return (Math.sqrt(xx*xx + yy*yy) < this.settings.graph.radius);
	}

	

	drawShape(x, y, size, sides, color, fill=false){
		this.ctx.beginPath();
		this.ctx.moveTo(x + size * Math.cos(0), y +size * Math.sin(0));
		for(let i=1; i<=sides; i++){
			this.ctx.lineTo(x + size * Math.cos(i * 2 * Math.PI / sides), y + size * Math.sin(i * 2 * Math.PI / sides));
		}

		this.ctx.strokeStyle = color;
		this.ctx.fillStyle = color;
		if(fill){
			this.ctx.fill();
		}else{
			this.ctx.stroke();
		}
		
	}

	drawLine(x1, y1, x2, y2, color){
		this.ctx.strokeStyle = color;
		this.ctx.beginPath();
		this.ctx.moveTo(x1,y1);
		this.ctx.lineTo(x2, y2);
		this.ctx.stroke();

	}

}

class DataObject{
	constructor(data, x, y, width, height){
		this.data = data;
		this.x = x;
		this.y = y;
		this.shape = 3;
		this.width = width;
		this.height = height;
		this.series = this.dataSeries();
		this.range = this.dataRange();
		this.points = this.dataPoints();
	}

	makeDataSet(){
		return{
			graph : {x:this.x, y:this.y},
			series : this.series,
			range : this.range,
			points : this.points,
		}
	}

	dataSeries(){
		let series = {}; // array of series objects
		let s = []; // quick list of series names 
		this.data.forEach(d=>{
			if(! s.includes(d.name)){
				s.push(d.name);
				series[d.name]={
					name: d.name,
					color: 'hsl('+ 360*Math.random() +',50%,50%)',
					shape: this.shape,
				};
				this.shape ++;
			}
		});
		return series;
	}

	dataRange(){
		let range = {}

		//sort by y value
		const yy = this.y; //this.y doesn't register in the sort function so had to store it locally
		this.data.sort(function(a, b){return a[yy] - b[yy]});
	
		range.ymin = Math.max(this.data[0][this.y], 0);
		range.ymax = this.data[this.data.length-1][this.y];

		//convert date to timestamp
		this.data.forEach(d=>{
			d.timestamp = Date.parse(d.date)/1000;
		});

		//sort the x value
		this.data.sort(function(a, b){return a.timestamp - b.timestamp});
		range.xmin = this.data[0].timestamp;
		range.xmax = this.data[this.data.length-1].timestamp;

		return range;
	}

	dataPoints(){
		let points = []
		this.data.forEach(d=>{
			points.push({
				x : Math.floor(((d.timestamp-this.range.xmin) / (this.range.xmax-this.range.xmin)) * this.width),
				y : Math.floor(((d[this.y] - this.range.ymin) / (this.range.ymax - this.range.ymin)) * this.height),
				label : `${d.name}: ${d[this.x]} - ${d[this.y]}`,
				series: d.name,
				color: this.series[d.name].color,
				shape: this.series[d.name].shape,
			})
			
			
		});
		return points;
	}
}


if(document.querySelector('#graph')){
	const canvas = document.querySelector('#graph').querySelector('#canvas');
	new Graph(canvas);
}