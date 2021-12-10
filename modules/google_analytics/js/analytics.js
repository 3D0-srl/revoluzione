$(document).ready(function(){
		if( $('#box_browsers').length > 0 ){
			
			load_widget_browsers();
		}
	});



	function load_widget_browsers(){
		$.ajax({
		  type: "GET",
		  url: "/modules/google_analytics/controller.php",
		  data: { action: "widget_statistic"},
		  dataType: "json",
		  success: function(data){
				if(data.result == 'ok'){
					 $('#box_browsers').html(data.content);

					var browsers = [];
					for( var iter in data.browsers){
						
						browsers.push(
							{
							'label': iter,
							'value': data.browsers[iter]
							}
						);
					}

					 //******************** DONUT CHART ********************//
					new Morris.Donut({
						element: 'donut-chart1',
						data: browsers,
						colors: ['#C75757', '#18A689', '#0090D9', '#2B2E33', '#0090D9'],
						formatter: function (x) {
							return x + "%"
						}
					});

					var returning_visitors = [];
					for( var iter = 0; iter<12; iter++){
						if( data.visitors_returning[iter] ){
							returning_visitors[iter] = new Array(parseInt(iter),data.visitors_returning[iter]);
						}else{
							returning_visitors[iter] = new Array(parseInt(iter),0);
						}
					}

					var new_visitors = [];
					for( var iter = 0; iter<12; iter++){
						if( data.visitors_new[iter] ){
							new_visitors[iter] = new Array(parseInt(iter),data.visitors_new[iter]);
						}else{
							new_visitors[iter] = new Array(parseInt(iter),0);
						}
					}
					
					

					  /****  Line Chart  ****/
						var graph_lines = [{
							label: "Line 1",
							data: returning_visitors,
							lines: {
								lineWidth: 2
							},
							shadowSize: 0,
							color: '#0090D9'
						}, {
							label: "Line 1",
							data: returning_visitors,
							points: {
								show: true,
								fill: true,
								radius: 6,
								fillColor: "#0090D9",
								lineWidth: 3
							},
							color: '#fff'
						}, {
							label: "Line 2",
							data: new_visitors,
							animator: {
								steps: 300,
								duration: 1000,
								start: 0
							},
							lines: {
								fill: 0.7,
								lineWidth: 0,
							},
							color: '#18A689'
						}, {
							label: "Line 2",
							data: new_visitors,
							points: {
								show: true,
								fill: true,
								radius: 6,
								fillColor: "#18A689",
								lineWidth: 3
							},
							color: '#fff'
						}, ];

					function lineCharts(){
						var line_chart = $.plotAnimator($('#graph-lines'), graph_lines, {
							xaxis: {
								tickLength: 0,
								tickDecimals: 0,
								min: 0,
								ticks: [
									[0, 'Gen'], [1, 'Feb'], [2, 'Mar'], [3, 'Apr'], [4, 'Mag'], [5, 'Giu'], [6, 'Lug'], [7, 'Ago'], [8, 'Set'],  [9, 'Ott'], [10, 'Nov'], [11, 'Dic']
								],
								font: {
									lineHeight: 12,
									weight: "bold",
									family: "Open sans",
									color: "#8D8D8D"
								}
							},
							yaxis: {
								ticks: 3,
								tickDecimals: 0,
								tickColor: "#f3f3f3",
								font: {
									lineHeight: 13,
									weight: "bold",
									family: "Open sans",
									color: "#8D8D8D"
								}
							},
							grid: {
								backgroundColor: {
									colors: ["#fff", "#fff"]
								},
								borderColor: "transparent",
								margin: 0,
								minBorderMargin: 0,
								labelMargin: 15,
								hoverable: true,
								clickable: true,
								mouseActiveRadius: 4
							},
							legend: {
								show: false
							}
						});
					}
					lineCharts();

					 /****  Bars Chart  ****/
					var graph_bars = [{
						// Visitors
						data: returning_visitors,
						color: '#0090D9'
					}, {
						// Returning Visitors
						data: new_visitors,
						color: '#18A689',
						points: {
							radius: 4,
							fillColor: '#008fc0'
						}
					}];
					function barCharts(){
						bar_chart = $.plotAnimator($('#graph-bars'), graph_bars, {
							series: {
								bars: {
									fill: 1,
									show: true,
									barWidth: .6,
									align: 'center'
								},
								shadowSize: 0
							},
							xaxis: {
								tickColor: 'transparent',
								ticks: [
									[0, 'Gen'], [1, 'Feb'], [2, 'Mar'], [3, 'Apr'], [4, 'Mag'], [5, 'Giu'], [6, 'Lug'], [7, 'Ago'], [8, 'Set'], [9, 'Ott'], [10, 'Nov'], [11, 'Dic']
								],
								font: {
									lineHeight: 12,
									weight: "bold",
									family: "Open sans",
									color: "#9a9a9a"
								}
							},
							yaxis: {
								ticks: 3,
								tickDecimals: 0,
								tickColor: "#f3f3f3",
								font: {
									lineHeight: 13,
									weight: "bold",
									family: "Open sans",
									color: "#9a9a9a"
								}
							},
							grid: {
								backgroundColor: {
									colors: ["#fff", "#fff"]
								},
								borderColor: "transparent",
								margin: 0,
								minBorderMargin: 0,
								labelMargin: 15,
								hoverable: true,
								clickable: true,
								mouseActiveRadius: 4
							},
							legend: {
								show: false
							}
						});
					}

					$("#graph-lines").on("animatorComplete", function () {
						$("#lines, #bars").removeAttr("disabled");
					});

					$("#lines").on("click", function () {
						$('#bars').removeClass('active');
						$('#graph-bars').fadeOut();
						$(this).addClass('active');
						$("#lines, #bars").attr("disabled", "disabled");
						$('#graph-lines').fadeIn();
						lineCharts();
					});

					$("#graph-bars").on("animatorComplete", function () {
						$("#bars, #lines").removeAttr("disabled")
					});

					$("#bars").on("click", function () {
						$("#bars, #lines").attr("disabled", "disabled");
						$('#lines').removeClass('active');
						$('#graph-lines').fadeOut();
						$(this).addClass('active');
						$('#graph-bars').fadeIn().removeClass('hidden');
						barCharts();
					});

					$('#graph-bars').hide();

				function showTooltip(x, y, contents) {
					$('<div id="flot-tooltip">' + contents + '</div>').css({
						position: 'absolute',
						display: 'none',
						top: y + 5,
						left: x + 5,
						color: '#fff',
						padding: '2px 5px',
						'background-color': '#717171',
						opacity: 0.80
					}).appendTo("body").fadeIn(200);
				};

				$("#graph-lines, #graph-bars").bind("plothover", function (event, pos, item) {
					$("#x").text(pos.x.toFixed(0));
					$("#y").text(pos.y.toFixed(0));
					if (item) {
						if (previousPoint != item.dataIndex) {
							previousPoint = item.dataIndex;
							$("#flot-tooltip").remove();
							var x = item.datapoint[0].toFixed(0),
								y = item.datapoint[1].toFixed(0);
							showTooltip(item.pageX, item.pageY, y + " visitatori");
						}
					} else {
						$("#flot-tooltip").remove();
						previousPoint = null;
					}
				});
				$(window).resize(function () {
					new Morris.Donut({
						element: 'donut-chart1',
						data: browsers,
						colors: ['#C75757', '#18A689', '#0090D9', '#2B2E33', '#0090D9'],
						formatter: function (x) {
							return x + "%"
						}
					});
				});
				
					
				}else{
					//MarionAlert(js_error_title_alert,data.error);
				}
		  }
		 
		});
	}

