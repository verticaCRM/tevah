
function FunnelWidget (argsDict) {
    var defaultArgs = {
    	title: "Sales Hiring",
    };
    auxlib.applyArgs (this, defaultArgs, argsDict);

  DataWidget.call (this, argsDict); 
  this.data = $.parseJSON(argsDict.chartData);
 
  var header = this.d3contentContainer.select('h1.amount');

      header.
  	    append('text').
  	    classed("currency-symbol", true).
  	  	text('$');

    	header.
  		append('text').
  		classed("number", true).
  		text('0');


  this.initialize();
}

FunnelWidget.prototype = auxlib.create (DataWidget.prototype);

// FunnelWidget.prototype.changeSource = function(value){
		
// 	data = this.chartData;
// 	data = data.map( function(d) { return d[0] });

// 	this.layers = this.addLayers(data);

// 	// console.debug(this.layers);
// 	this.drawLayers();
// 	this.buildAmount();
// }

FunnelWidget.prototype.initialize = function(){
	var chart = this.d3contentContainer.select('.chart-funnel');
	
	this.x = 50;
	this.y = 0;
	this.width  = chart[0][0].offsetWidth/2;
	this.height = chart[0][0].offsetWidth/2;
    
    this.svg = chart.append('svg')
					.attr('width', this.width+100+'px')
					.attr('height', this.height+'px');
	


	// var data = this.data.map( function(d) { return d });
	var data = this.chartData.map( function(d){ return { name: 'layer', amount: d[0] }; } );

	this.layers = this.addLayers(data);

	this.addLayers(data);

	this.buildAmount();

    this.drawLayers();

}

FunnelWidget.prototype.buildAmount = function() {
		var header = this.d3contentContainer.select('h1.amount');

	    var sum = this.sum 
	    header.select('.number').
	    transition().
	    delay(0).
	    duration(500).
	    tween("text", function() {
	      var i = d3.interpolateRound(0, sum);
	      return function(t) {
	        this.textContent = i(t);
	      };
	    });
}


// Layer object: 
// { name, amount }
FunnelWidget.prototype.addLayers = function(data){

	this.layer_height = this.height / data.length;
	this.layer_count = data.length;
	this.max = d3.max(data, function(l){ return l.amount });

	this.sum = 0.0;
	for(var i in data){
		this.sum += data[i].amount;
	}

	this.color = d3.scale.linear()
		.domain([0,data.length-1])
		.interpolate(d3.interpolateRgb)
		.range(["#ED5A4F",  "#8BE76E"])

	Layer = this.Layer();

	this.layers = data.map(function(d){return new Layer(d)});

	return this.layers;

}

FunnelWidget.prototype.renderText = function(letter){
	that = this;

	if (letter === 'x'){
		return function(d){
			var nextLayer = d.nextLayer(); 
			var length = this.getComputedTextLength()

			if( length  >  d.width){
				d.outside = true;
				return (d.x + nextLayer.x)/2.0 - length - 10; 
			}

			d.outside = false;
			return that.x + that.width/2 - length/2
		}	
	}	

	else if (letter === 'y') {
		return function(d){ 
			return d.y + 0.75 * d.height;
		}
	}

}

FunnelWidget.prototype.drawLayers = function(){
	that = this;


	this.svg.selectAll("path").data([]).exit().remove();

	this.svg.selectAll("path").
		data(this.layers).
		enter().
		append('path').
		attr('d', function(d){ return d.nullPath() }).
		attr('fill', function(d){ return d.color }).
			transition().
			attr('d', function(d){ return d.pathString() }).
			duration(500).
			delay(0);

	this.svg.selectAll("text").data([]).exit().remove();

	this.svg.selectAll("text").
		data(this.layers).
		enter().
		append('text').
			html(function(d){ return d.amount }).
			style('font-size', function(d) { return d.height*.75 }).
			attr('x', this.renderText('x') ).
			attr('y', this.renderText('y') ).
			classed('funnel-text', true).
			classed('outside', function(d){ return d.outside });
}




FunnelWidget.prototype.Layer = function(){
	that = this;
	that.counter = 0;

	function Layer(object){
		this.name    = object.name;
		this.amount  = object.amount;
		this.number  = that.counter++;
		this.color   = that.color(this.number);
		this.width   = this.amount / that.max * that.width;
		this.height  = that.layer_height;
		this.x       = that.width/2 - this.width/2 + that.x;
		this.y       = this.number * this.height + that.y;
		this.outside = false;
	}

	Layer.prototype.nextLayer = function(){
		if( that.layers.length - 1 == this.number )
			return this;

		return that.layers[this.number + 1];
	}

	Layer.prototype.prevLayer = function(){
		if( this.number == 0 )
			return this;
		
		return that.layers[this.number - 1];
	}

	Layer.prototype.draw = function(){
		that.svg.append('rect').
			attr('x', this.x).
			attr('y', this.y).
			attr('width', this.width).
			attr('height', this.height).
			attr('fill', this.color);	


	}

	Layer.prototype.nullPath = function(){
		var zero = this.x + this.width/2

		var path = [{ type: 'M', 
					  x: zero, 
					  y: this.y
			}];

		path.push({ type: 'C',
					x1: zero,
					y1: this.y+this.height,
					x2: zero,
					y2: this.y+this.height,
					x: zero,
					y: this.y+this.height
		});

		path.push({ type: 'L',
					x: zero,
					y: this.y+this.height
		});

		path.push({ type: 'C',
					x1: zero,
					y: this.y+this.height,
					x2: zero,
					y2: this.y+this.height,
					x: zero,
					y1: this.y,
		});

		path.push({ type: 'L',
					x: zero,
					y: this.y
		});

		var path_str = "";
		for (var i in path){
			for(key in path[i]){
				path_str += path[i][key] + " ";
			}

		}

		return path_str;
	}

	Layer.prototype.pathString = function(){
		var nextLayer = this.nextLayer(); 

		if (this.number == nextLayer.number){
			var w = this.width * .7

			nextLayer = { width : w,
						  x: that.x + that.width/2 - w/2,
						  y: this.height + this.y,
						 };
		}

		function reflect(x){
			return that.x + that.width - (x - that.x);
		}

		// Bezier Handles
		// (0,0) will be the top left corner of this layer
		// (1,1) will be the top left corner of the next layer
		var cp1x = 0.2 * (nextLayer.x - this.x) + this.x;
		var cp1y = 0.8 * (nextLayer.y - this.y) + this.y;

		var cp2x = 0.5 * (nextLayer.x - this.x) + this.x;
		var cp2y = 0.8 * (nextLayer.y - this.y) + this.y;

		var path = [{ type: 'M', 
					  x: this.x, 
					  y: this.y
			}];

		path.push({ type: 'C',
					x1: cp1x,
					y1: cp1y,
					x2: cp2x,
					y2: cp2y,
					x: nextLayer.x,
					y: nextLayer.y,
		});

		path.push({ type: 'L',
					x: nextLayer.x + nextLayer.width,
					y: nextLayer.y
		});

		path.push({ type: 'C',
					x1: reflect(cp2x),
					y1: cp2y,
					x2: reflect(cp1x),
					y2: cp1y,
					x: this.x + this.width,
					y: this.y,
		});

		path.push({ type: 'L',
					x: this.x,
					y: this.y
		});

		var path_str = "";
		for (var i in path){
			for(key in path[i]){
				path_str += path[i][key] + " ";
			}

		}

		return path_str;
	}

	Layer.prototype.renderCurve = function(path){

			return d3.select('html').append('path').
				attr('id', 'layer-path-'+this.number).
				attr('d', path_str).
				attr('fill', this.color).
				on('mouseover', function() { layer.mouseover(this) } ).
				on('mouseout',  function() { layer.mouseout(this) } );
	}
	


	Layer.prototype.view = function(){
		return that.svg.select('#layer-path-'+this.number);
	}

	Layer.prototype.mouseover = function(view){
		d3.select(view).attr('fill', 'darkgrey');
	}

	Layer.prototype.mouseout = function(view){
		d3.select(view).attr('fill', this.color);
	}

	return Layer;

}
