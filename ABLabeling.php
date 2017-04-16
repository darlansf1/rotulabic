<?php include_once 'includes/labeling.inc.php'; include_once 'includes/suggestion.php';?>

<!DOCTYPE html>
<html lang="pt">
    <head> 
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="author" content="Rafael Paravia, Darlan Santana Farias">
		<title>Rotular</title>
		
         <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
		<script src="js/jquery.min.js"></script>
		<!-- RangyInputs (useful for dealing with selection on TextAreas) -->
		<script src="js/rangyinputs-jquery-src.js"></script>
		<!-- Bootstrap -->
		<link rel="stylesheet" href="styles/bootstrap.min.css">
		<link rel="stylesheet" href="styles/bootstrap-theme.min.css">
		<script src="js/bootstrap.min.js"></script>
		
		<link rel="stylesheet" href="styles/main.css" />
		<link rel="stylesheet" href="styles/sticky-footer-navbar.css" />
		
		<script type="text/Javascript">
			var polarities = <?php getLabels($mysqli);?>;
			var colors = <?php getColors($mysqli);?>;
			var aspect_suggestions = <?php $aspects = getAspectSuggestionsJS($mysqli);?>;
			var aspect_polarities = <?php getAspectPolaritiesJS($mysqli, $aspects);?>;
			var implicitCount = 0, generalCount = 0, normalCount = 0;
			var globalSubmit = true;
			var lastPolarity = [];
			var previousAspects = <?php getAspectItems($mysqli, 4); ?>;
			var previousAspects_polarities = <?php getAspectItems($mysqli, 5);?>;
			var previousAspects_starts = <?php getAspectItems($mysqli, 6);?>;
			var previousAspects_types = <?php getAspectItems($mysqli, 3);?>;
			var previousSIs = {
				numberOfSIs: <?php getNumberOfSIs($mysqli); ?>,
				real_texts: <?php getSIItems($mysqli, 1); ?>,
				terms: <?php getSIItems($mysqli, 0); ?>,
				starts: <?php getSIItems($mysqli, 2) ?>
			};
			var showSkipMessage = <?php echo (isset($_SESSION['showSkipMessage'])? $_SESSION['showSkipMessage']: 'true');?>;
			
			$(document).ready(function(){
				
			});
			
			function getAspectStartIndexByRow(rowIndex){
				var text = $('#hidden_text').html();
				text = decodeText(text);
				var row = $('#aspect_table').get(0).rows[rowIndex];
				var type = row.cells[0].getAttribute('type');
				var aspect = $(row.cells[0]).html();
				aspect = decodeText(aspect);
				var occurrNumber = row.cells[0].getAttribute('occurrence');
				
				var found = 0;
				var index, startIndex = 0;
				if(type == 0){
					return findBeginningByOccurence(text, aspect, occurrNumber);
				}
				return 0;
			}
			
			function getSIStartIndex(si, occur){
				var text = $('#hidden_text').html();
				text = decodeText(text);
				
				return findBeginningByOccurence(text, si, occur);
			}
			
			function getNumberOfSIsByRow(rowIndex){		
				var row = $('#aspect_table').get(0).rows[rowIndex];
				return ($(row.cells[2]).get(0).childNodes.length-1);
			}
			
			function getSIsByRow(rowIndex){
				var SIs = [], SI_starts = [], SI_ends = [], SI_texts = [];
				var row = $('#aspect_table').get(0).rows[rowIndex];
				var children = $(row.cells[2]).get(0).childNodes;
				
				for(var i = 0; i < children.length-1; i++){
					SIs[i] = decodeText($(children[i]).html()).split('<')[0];
					SI_texts[i] = children[i].getAttribute('real_text');
					SI_starts[i] = findBeginningByOccurence(decodeText($('#hidden_text').html()), SI_texts[i], children[i].getAttribute('occurrence'));
					SI_ends[i] = SI_starts[i]+SI_texts[i].length;
				}
				
				return {
					terms: SIs, 
					real_texts: SI_texts,
					starts: SI_starts, 
					ends: SI_ends
				};
			}
			
			function cleanHiddenInputs(numberOfSIs){
				var elements = document.getElementsByName('numberOfRows');				
				while(elements.length != 0){
					elements.item(0).parentNode.removeChild(elements.item(0));
				}
				
				var elements = document.getElementsByName('aspect_aspect[]');				
				while(elements.length != 0){
					elements.item(0).parentNode.removeChild(elements.item(0));
				}
				
				var elements = document.getElementsByName('aspect_polarity[]');				
				while(elements.length != 0){
					elements.item(0).parentNode.removeChild(elements.item(0));
				}
				
				var elements = document.getElementsByName('aspect_start[]');				
				while(elements.length != 0){
					elements.item(0).parentNode.removeChild(elements.item(0));
				}
				
				var elements = document.getElementsByName('aspect_end[]');				
				while(elements.length != 0){
					elements.item(0).parentNode.removeChild(elements.item(0));
				}
				
				var elements = document.getElementsByName('aspect_type[]');				
				while(elements.length != 0){
					elements.item(0).parentNode.removeChild(elements.item(0));
				}
				
				//alert("numberOfSIs > 0 ?" +(numberOfSIs > 0));
				if(numberOfSIs > 0){
					var elements = document.getElementsByName('numberOfSIs[]');				
					while(elements.length != 0){
						elements.item(0).parentNode.removeChild(elements.item(0));
					}
					
					var elements = document.getElementsByName('SIs[]');				
					while(elements.length != 0){
						elements.item(0).parentNode.removeChild(elements.item(0));
					}
				
					var elements = document.getElementsByName('SI_starts[]');				
					while(elements.length != 0){
						elements.item(0).parentNode.removeChild(elements.item(0));
					}
					
					var elements = document.getElementsByName('SI_ends[]');				
					while(elements.length != 0){
						elements.item(0).parentNode.removeChild(elements.item(0));
					}
				}
				
			}
			
			function appendAnnotations(){
				var table = $('#aspect_table').get(0).rows;
				
				var numRow = Number(table.length);
				
				var numberOfRows = $("<input>")
					.attr("type", "hidden")
					.attr("name", "numberOfRows").val(numRow-1);
					$('#labelingForm').append($(numberOfRows))
				
				//alert("number of aspects: " + (numRow-1));
				
				var i, j, input, row, select, start, numberOfSIs, SIs, totalSIs = 0;
				
				for(i = 1; i < numRow; i++){
					input = [];
					row = table[i];
					
					input[0] = document.createElement('input');
					input[0].setAttribute("name", "aspect_aspect[]");
					input[0].setAttribute("value", decodeText($(row.cells[0]).html()));
					
					input[1] = document.createElement('input');
					input[1].setAttribute("name", "aspect_type[]");
					input[1].setAttribute("value", row.cells[0].getAttribute('type'));
					
					input[2] = document.createElement('input');
					input[2].setAttribute("name", "aspect_polarity[]");
					select = $(row.cells[1]).get(0).childNodes[0];
					input[2].setAttribute("value", select.options[select.selectedIndex].text);
					
					start = getAspectStartIndexByRow(i);
					
					numberOfSIs = getNumberOfSIsByRow(i);
					totalSIs = totalSIs+numberOfSIs;
					SIs = getSIsByRow(i);
					
					if(row.cells[0].getAttribute('type') != 0)
						start = SIs.starts[0];
					
					input[3] = document.createElement('input');
					input[3].setAttribute("name", "aspect_start[]");
					input[3].setAttribute("value", start);
					
					input[4] = document.createElement('input');
					input[4].setAttribute("name", "aspect_end[]");
					
					input[5] = document.createElement('input');
					input[5].setAttribute("name", "numberOfSIs[]");
					input[5].setAttribute("value", numberOfSIs);
					
					if(row.cells[0].getAttribute('type') == 0)
						input[4].setAttribute("value", start+decodeText($(row.cells[0]).html()).length);
					else{ 
						//alert("SI: " + SIs.terms[0]);
						//alert("totalSIs: "+totalSIs);
						input[4].setAttribute("value", start);
						
						//hidden or general aspect must have at least 1 sentiment indication
						if(numberOfSIs == 0){
							globalSubmit = false;
							cleanHiddenInputs(totalSIs);
							return;	
						}
					}
					
					for(j = 0; j < input.length; j++){
						input[j].setAttribute("type", "hidden");
						$('#labelingForm').append($(input[j]));
					}
					
					//alert("number of SIs: "+numberOfSIs);
					
					for(j = 0; j < numberOfSIs; j++){
						input = document.createElement('input');
						input.setAttribute("type", "hidden");
						input.setAttribute("name", "SIs[]");
						input.setAttribute("value", SIs.terms[j]);
						$('#labelingForm').append($(input));
						
						input = document.createElement('input');
						input.setAttribute("type", "hidden");
						input.setAttribute("name", "SI_real_texts[]");
						input.setAttribute("value", SIs.real_texts[j]);
						$('#labelingForm').append($(input));
						
						input = document.createElement('input');
						input.setAttribute("type", "hidden");
						input.setAttribute("name", "SI_starts[]");
						input.setAttribute("value", SIs.starts[j]);
						$('#labelingForm').append($(input));
						
						input = document.createElement('input');
						input.setAttribute("type", "hidden");
						input.setAttribute("name", "SI_ends[]");
						input.setAttribute("value", SIs.ends[j]);
						$('#labelingForm').append($(input));
					}
				}

			}
			
			function validateForm(btnSubmit){
				var submit = true;
				if(btnSubmit==='jump' && showSkipMessage){
					if(!confirm("This document will be left unlabeled, unless it has been previously annoted. If left unlabeled, it will be counted as a no-aspect document. This message won't appear again, but you can always return to the previous documents if you ever change your mind about one you have skipped.")){
						submit = false;
					}
					<?php $_SESSION['showSkipMessage'] = 'false'; ?>;
					showSkipMessage = false;
				}else if(btnSubmit==='next'){
					if(	($('#aspect_table').get(0).rows.length <= 1)){
						alert("You must label at least one aspect, or skip the document to set it as a no-aspect document.");
						submit = false;
					}else{
						globalSubmit = true;
						appendAnnotations();
						if(!globalSubmit){
							alert("Hidden and General aspects need at least one sentiment word");
						}
						submit = globalSubmit;
					}
				}
				if(submit){
					var input = $("<input>")
					.attr("type", "hidden")
					.attr("name", "btnSubmit").val(btnSubmit);
					$('#labelingForm').append($(input)).submit();
				}
			};	
			
			function makeEditableAndHighlight(command, colour) {
				var range, sel = window.getSelection();
				
				if (sel.rangeCount && sel.getRangeAt) {
					range = sel.getRangeAt(0);
				}
				document.designMode = "on";
				if (range) {
					sel.removeAllRanges();
					sel.addRange(range);
				}
				// Use HiliteColor since some browsers apply BackColor to the whole block
				if (!document.execCommand(command, false, colour)) {
					document.execCommand(command, false, colour);
				}
				document.designMode = "off";
			}

			function highlight(command, colour) {
				if(command == 'foreColor'){
					colour = addHexColor(colour, -0x33);
				}else{
					colour = addHexColor(colour, 0x33);
				}
				
				var range, sel;
				$("#faketextarea").get(0).focus();
				
				if (window.getSelection) {
					// IE9 and non-IE
					try {
						if (!document.execCommand(command, false, colour)) {
							makeEditableAndHighlight(command, colour);
						}
					} catch (ex) {
						makeEditableAndHighlight(command, colour)
					}
				} else if (document.selection && document.selection.createRange) {
					// IE <= 8 case
					range = document.selection.createRange();
					range.execCommand(command, false, colour);
				}
				
				removeAllWhiteSpans();
			}
			
			function removeSentimentIndication(txt, count, siTxt, siOccurrence, occurrence, type){

				rowIndex = getRowIndex(type, count);
				
				if(rowIndex < 0)
					return;
				
				var polarity = getPolarity(rowIndex);
				var color = colors[polarities.indexOf(polarity)];
				
				var text = $('#faketextarea').html();
				text = decodeText(text);
				var table = $('#aspect_table').get(0);
				var row = table.rows[rowIndex];

				var sentimentIndication = $(row.cells[2]).get(0);
				
				var child;
				var hexColor = color;
				var tagBeggining = '<font color= ';
				var tagEnd = '</font>';
				var offset = 2;
				var i, j, occur = 0, startIndex = 0, index, cellIndex, childIndex;
				//alert('length: '+sentimentIndication.childNodes.length);
				//alert('lastChild: '+sentimentIndication.lastChild);
				for(i = 0; i < sentimentIndication.childNodes.length-1; i++){
					child = sentimentIndication.childNodes[i];
					//alert('child: '+child.getAttribute("occurrence"));

					if(/*$(child).html().split('<')[0]*/child.getAttribute('real_text') == siTxt && child.getAttribute("occurrence") == siOccurrence){
						if ( (index = findBeginningByOccurence(text, siTxt, siOccurrence)) > -1) {
							j = index;
							while(text.substring(j-1, j) != '<')
								j--;
							j--;
							$('#faketextarea').html(text.substring(0, j)+siTxt+text.substring(index+siTxt.length+tagEnd.length));
						}
						childIndex = i;
						break;
					}
				}
				
				sentimentIndication.removeChild(sentimentIndication.childNodes[childIndex]);
				
				removeAllHighlights();
				restoreHighlights();
			}
			
			function restoreHighlights(){
				var table = $('#aspect_table').get(0);
				
				for(var i = 1; i < table.rows.length; i++){
					changePolarityByIndex(i);
				}
			}
			
			//sums value to each of the RGB components of the color
			//sets component to 0 if result is negative and to FF if it gets over FF
			function addHexColor(color, value) {
				redValue = "0x" + color.substring(1, 3);
				greenValue = "0x" + color.substring(3, 5);
				blueValue = "0x" + color.substring(5);

				redValue = parseInt(redValue , 16);
				greenValue = parseInt(greenValue , 16);
				blueValue = parseInt(blueValue , 16);

				redValue = Math.min(redValue + value, 0xFF);
				greenValue = Math.min(greenValue + value, 0xFF);
				blueValue = Math.min(blueValue + value, 0xFF);
				
				redValue = Math.max(redValue, 0x0);
				greenValue = Math.max(greenValue, 0x0);
				blueValue = Math.max(blueValue, 0x0);
				
				redValue = redValue.toString(16); 
				if(redValue.length == 1)
					redValue = '0'+redValue;
				greenValue = greenValue.toString(16); 
				if(greenValue.length == 1)
					greenValue = '0'+greenValue;
				blueValue = blueValue.toString(16); 
				if(blueValue.length == 1)
					blueValue = '0'+blueValue;
				
				hexValue = redValue+greenValue+blueValue;
				
				return '#'+hexValue;
			}
			
			function selectText(start, end){
				//alert('start: '+start);
				//alert('end: '+end);
				var range = document.createRange();
				var node = $('#faketextarea').get(0).childNodes[0];
				range.selectNode(node);
				var selection = window.getSelection();
				selection.removeAllRanges();
				selection.addRange(range);
				
				range.setStart(node, start);
				range.setEnd(node,Math.min(end, node.length));
					
				selection.removeAllRanges();
				selection.addRange(range);
				return selection;
			}
			
			function addSentimentIndication(occurrence, aspect, count, type){
				var index;

				index = getRowIndex(type, count);

				var color = colors[polarities.indexOf(getPolarity(index))];
				var selection = window.getSelection();
				
				var parent = getSelectionParentElement(selection);
				
				if(selection.toString().length == 0 || !testSelectionValidity(parent)){
					alert('Mark the text for the sentiment word before pressing the + button');
					return;
				}

				var annotedText = $('#faketextarea').html();
				annotedText = decodeText(annotedText);
				var txt = processSelection(selection, getOccurrenceNumber(selection.toString(), getBeginning()));
				selection = window.getSelection();
				var occur = getOccurrenceNumber(txt, getBeginning());
				var originalBeginning = getBeginning();
				var innerAspect = "";
				var indexAspect;
				if(insideAnother(occur, txt, annotedText)){
					var start = findBeginningByOccurence(decodeText($('#hidden_text').html()), txt, occur);
					indexAspect = getSINewBeginning(start, txt.length+start);
					
					if(indexAspect[0] < 0){
						alert('You can not select this part of the text because it is part of an aspect or sentiment word.');
						restoreHighlights();
						return;
					}
					innerAspect = indexAspect[1];
					
					selectText(Math.min(indexAspect[0], originalBeginning), Math.max(originalBeginning+txt.length, indexAspect[0]+indexAspect[1].length));

					selection = window.getSelection();
					txt = processSelection(selection, getOccurrenceNumber(selection.toString(), getBeginning()));
					
					if(!txt || txt.length == 0){
						alert('Something is wrong with the selection.');
						restoreHighlights();
						return;
					}
				}
				
				var beginning = getBeginning();
				
				if(txt == null || txt.length == 0){
					alert('Invalid selection');
					restoreHighlights();
					return;
				}if(txt.length > 100){
					alert('Selection too long');
					restoreHighlights();
					return;
				}
				
				if(type == 0 && txt == aspect){
					alert("You can't have the same part of the text as an aspect and a sentiment word.");
					restoreHighlights();
					return;
				}
				
				/*added from here*/
				var text;
				if(innerAspect.length == 0){
					text = (txt);
				}else{
					var parts = txt.split(innerAspect);
					if(parts[0].length == 0)
						parts = [parts[1]];
					if(parts.length > 1 && parts[1].length == 0)
						parts = [parts[0]];
					
					var part1Index = txt.indexOf(parts[0]);
					text = (parts[0]);
					
					if(parts.length > 1){
						text = (parts[0]+parts[1]);
					}
				}
				
				appendSentiment($('#aspect_table').get(0).rows[index], txt, text, beginning)
				//to here
				/*cell = $('#aspect_table').get(0).rows[index].cells[2];
				var div = document.createElement('div');
				var siOccurrence = getOccurrenceNumber(txt, beginning);
				div.setAttribute('occurrence', siOccurrence);
				div.setAttribute('real_text', txt);
				var text;
				if(innerAspect.length == 0){
					text = document.createTextNode(txt);
				}else{
					var parts = txt.split(innerAspect);
					if(parts[0].length == 0)
						parts = [parts[1]];
					if(parts.length > 1 && parts[1].length == 0)
						parts = [parts[0]];
					
					var part1Index = txt.indexOf(parts[0]);
					text = document.createTextNode(parts[0]);
					
					if(parts.length > 1){
						text = document.createTextNode(parts[0]+parts[1]);
					}
				}
				
				var SIs = getSIsByRow(index);
				
				for(var k = 0; k < getNumberOfSIsByRow(index); k++){
					if(SIs.real_texts[k] == txt && SIs.starts[k] == findBeginningByOccurence($('#hidden_text').html(), txt, siOccurrence)){
						alert("The sentiment word has already been included for this aspect.");
						restoreHighlights();
						return;
					}
				}
				
				var addButton = cell.lastChild;
				cell.removeChild(cell.lastChild);
				div.appendChild(text);
				
				var closeButton = document.createElement('input');
				closeButton.setAttribute('value', 'x');
				closeButton.setAttribute('type', 'button');

				closeButton.setAttribute('id', 'siRemoveButton');
				closeButton.setAttribute("onclick", 'removeSentimentIndication(\"'+aspect+'\", '+count+', \"'+txt+'\", '+siOccurrence+', '+occurrence+', '+type+')');

				div.appendChild(closeButton);
			
				cell.appendChild(div);
				cell.appendChild(addButton);
				*/
				restoreHighlights();
			}
			
			function getRowIndex(aspect, polarity, occurrence){
				var table = $('#aspect_table').get(0);
				var row;
				var rowAspect;
				
				for(var i = 0; i < table.rows.length; i++){
						row = table.rows[i];
						
						rowAspect = decodeText($(row.cells[0]).html());
						
						if(aspect != rowAspect)
							continue;
						
						if(row.cells[0].getAttribute('occurrence') == occurrence && getPolarity(i) == polarity)
							return i;
				}
				
				return -1;
			}
			
			function getRowIndexByCount(aspect, count, occurrence){
				var table = $('#aspect_table').get(0);
				var row;
				var rowAspect;
				var result = [-1, 0];
				var polarity;
				
				for(var i = 0; i < table.rows.length; i++){
						row = table.rows[i];
						
						rowAspect = decodeText($(row.cells[0]).html());
						rowCount = $(row.cells[0]).get(0).getAttribute('count');
						
						if(aspect != rowAspect)
							continue;
						
						if(row.cells[0].getAttribute('occurrence') == occurrence &&  rowCount == count){
							result[0] = i;
							
							polarity = getPolarity(i);
							for(var j = 0; j < table.rows.length; j++){
								row = table.rows[j];
								rowAspect = decodeText($(row.cells[0]).html());
								if(rowAspect == aspect && row.cells[0].getAttribute('occurrence') == occurrence && getPolarity(j) == polarity)
									result[1]++;
							}
							
							return result;
						}
				}
				
				return result;
			}
			
			function getRowIndex(type, count){
				var table = $('#aspect_table').get(0);
				var row;
				var aspectType, aspectCount;
				
				for(var i = 0; i < table.rows.length; i++){
						row = table.rows[i];
						
						aspectType = $(row.cells[0]).get(0).getAttribute('type');
						
						if(type != aspectType)
							continue;
						
						aspectCount = $(row.cells[0]).get(0).getAttribute('count');
						if(count == aspectCount)
							return i;
				}
				
				return -1;
			}
			
			function getPolarity(rowIndex){
				var table = $('#aspect_table').get(0);
				var row = table.rows[rowIndex];
				var polarityCell = $(row.cells[1]).get(0);
				var select = polarityCell.childNodes[0];
				
				return select.options[select.selectedIndex].text;
			}
			
			function removeAllRows(){
				var table = $('#aspect_table').find("tr:gt(0)").remove();
				removeAllHighlights();
				restoreHighlights();
			}
			function removeRow(txt, count, occurrence, type){
				var rowIndex;
				
				rowIndex = getRowIndex(type, count);
				
				if(rowIndex < 0)
					return;
				
				var table = $('#aspect_table').get(0);
				
				table.deleteRow(rowIndex);
				removeAllHighlights();
				restoreHighlights();
			}
			
				$(function() {
				  $("#polarities_dd").on("click",(function(){addRow(0);}));
				});
				
				$(function() {
				  $("#implicitButton").on("click",(function(){addRow(1);}));
				});
				
				$(function() {
				  $("#generalButton").on("click",(function(){addRow(2);}));
				});
				
				$(function() {
				  $("#removeAll").on("click",(function(){removeAllRows();}));
				});
				
				function appendRow(txt, polarity, beginning, type){
					
					var table = $('#aspect_table').get(0);
					
					var row = document.createElement('tr');		
					var cell1 = document.createElement('td');
					var cell2 = document.createElement('td');
					var cell3 = document.createElement('td');
					var cell4 = document.createElement('td');
					var div = document.createElement('div');
						
					var aspect = document.createTextNode(txt);
						
					var polaritySelect = document.createElement('select');
					var polOption = document.createElement('option');
					polOption.text = (polarity);
					polaritySelect.appendChild(polOption);
					polaritySelect.setAttribute('id', 'polaritySelect');
						
					for(var i = 0; i < polarities.length; i++){
						if(polarities[i] != polarity){
							polOption = document.createElement('option');
							polOption.text = (polarities[i])
							polaritySelect.appendChild(polOption);
						}
					}
							
					var addButton = document.createElement('input');
					addButton.setAttribute('type', 'button');
					addButton.setAttribute('id', 'addButton');
					addButton.setAttribute('value', '+');

					var closeButton = document.createElement('input');
					closeButton.setAttribute('type', 'button');
					closeButton.setAttribute('id', 'removeButton');
					closeButton.setAttribute('value', 'x');
					var occurrence = getOccurrenceNumber(txt, beginning);
					var counter = (type == 1)? implicitCount : generalCount;
					
					if(type == 1){
						cell1.setAttribute('count', implicitCount);
						implicitCount++;
					}else if(type == 2){
						cell1.setAttribute('count', generalCount);
						generalCount++;
					}else{
						cell1.setAttribute('count', normalCount);
					}
					
					if(type == 0){
						lastPolarity[normalCount] = polarities.indexOf(polarity);
						counter = normalCount;
						normalCount++;
						polaritySelect.setAttribute("onchange", 'changePolarity('+occurrence+', \"'+txt+'\",'+counter+')');
					}else
						polaritySelect.setAttribute("onchange", 'changeOtherPolarity(\"'+type+'\", '+counter+')');
					
					addButton.setAttribute("onclick", 'addSentimentIndication('+occurrence+', \"'+txt+'\", '+counter+','+type+')');
						
					closeButton.setAttribute("onclick", 'removeRow(\"'+txt+'\", '+counter+','+occurrence+','+type+')');
						
					cell1.setAttribute('occurrence', getOccurrenceNumber(txt, beginning));
					cell1.setAttribute('type', type);
						
					cell1.appendChild(aspect);
						
					cell3.appendChild(addButton);
					cell2.appendChild(polaritySelect);
					cell4.appendChild(closeButton);
						
					row.appendChild(cell1);
					row.appendChild(cell2);
					row.appendChild(cell3);
					row.appendChild(cell4);
						
					table.appendChild(row);
					
					return row;
				}
				
				function appendSentiment(row, real_text, term, beginning){
					var aspect = decodeText($(row.cells[0]).html());
					var type = $(row.cells[0]).get(0).getAttribute('type');
					var occurrence = $(row.cells[0]).get(0).getAttribute('occurrence');
					var count = $(row.cells[0]).get(0).getAttribute('count');
					
					var index = Array.prototype.slice.call($('#aspect_table').get(0).rows).indexOf(row);
					
					var cell = row.cells[2];
					var txt = real_text;
					var div = document.createElement('div');
					
					var siOccurrence = getOccurrenceNumber(txt, beginning);
					//alert('si occur: '+siOccurrence);
					
					div.setAttribute('occurrence', siOccurrence);
					div.setAttribute('real_text', txt);
					
					var text = document.createTextNode(term);
					
					var SIs = getSIsByRow(index);
					
					for(var k = 0; k < getNumberOfSIsByRow(index); k++){
						if(SIs.real_texts[k] == txt && SIs.starts[k] == findBeginningByOccurence(decodeText($('#hidden_text').html()), txt, siOccurrence)){
							alert("The sentiment word has already been included for this aspect.");
							restoreHighlights();
							return false;
						}
					}
					
					var addButton = cell.lastChild;
					cell.removeChild(cell.lastChild);
					div.appendChild(text);
					
					var closeButton = document.createElement('input');
					closeButton.setAttribute('value', 'x');
					closeButton.setAttribute('type', 'button');

					closeButton.setAttribute('id', 'siRemoveButton');
					closeButton.setAttribute("onclick", 'removeSentimentIndication(\"'+aspect+'\", '+count+', \"'+txt+'\", '+siOccurrence+', '+occurrence+', '+type+')');

					div.appendChild(closeButton);
				
					cell.appendChild(div);
					cell.appendChild(addButton);
					
					return true;
				}
				
				function loadPreviousAnnotations(){
					var offset = 0;
					//alert(previousAspects.length);
					for(var k = 0; k < previousAspects.length; k++){
						var txt = previousAspects[k];
						var polarity = previousAspects_polarities[k];
						var beginning = previousAspects_starts[k];
						var type = previousAspects_types[k];
						if(type == "normal")
							type = 0;
						else if(type == "hidden")
							type = 1;
						else
							type = 2;
						
						//alert(k);
						var row = appendRow(txt, polarity, beginning, type);
						
						for(var j = 0; j < previousSIs.numberOfSIs[k]; j++){
							appendSentiment(row, previousSIs.real_texts[offset+j], previousSIs.terms[offset+j], previousSIs.starts[offset+j]);
						}
						offset+=j;
					}
				}
				
				function addSuggestions(){
					if(normalCount == 0 && implicitCount == 0 && generalCount == 0){
						for(var i = 0; i < aspect_suggestions.length; i+=2){
							
							var selection = selectText(Number(aspect_suggestions[i+1]), Number(aspect_suggestions[i+1]+aspect_suggestions[i].length))

							addRowFromSelection(selection, ((aspect_polarities.length > 0)? aspect_polarities[i/2] : ""));
							selection.removeAllRanges();
						}
					}

					restoreHighlights();
				}
				
				function getSelectionParentElement() {
					var parentEl = null;

					sel = window.getSelection();
					if (sel.rangeCount) {
						parentEl = sel.getRangeAt(0).commonAncestorContainer;
						if (parentEl.nodeType != 1) {
							parentEl = parentEl.parentNode;
						}
					}
					
					return parentEl;
				}
				
				function decodeText(text){
					var elem = document.createElement('textarea');
					elem.innerHTML = text;
					return elem.value;
				}
				
				function getOccurrenceNumber(str, beginning){
					var text = $('#hidden_text').html();
					text = decodeText(text);
				
					var startIndex = 0;
					var index, count = 0;
					while((index = text.indexOf(str, startIndex)) > -1){
						//alert('found at index: '+index+', beginning: '+beginning+', text: '+text);
						if(index == beginning){
							return count;
						}
						
						count++;
						startIndex = index+1;
					}
					return -1;
				}
				
			function changeOtherPolarity(type, count){
				var rowIndex = getRowIndex(type, count);
				changePolarityByIndex(rowIndex);
			}
			
			function changePolarity(occurrence, txt, count){
				var rowIndex = getRowIndexByCount(txt, count, occurrence);
				
				if(rowIndex[1] == 1){
					lastPolarity[count] = getPolarity(rowIndex[0]);
					removeAllHighlights();
					restoreHighlights();
				}else{
					alert('The polarity you tried to change to has already been assigned to this aspect in another row.');
					var table = $('#aspect_table').get(0);
					var row = table.rows[rowIndex[0]];
					var polarityCell = $(row.cells[1]).get(0);
					var select = polarityCell.childNodes[0];
				
					select.selectedIndex = lastPolarity;
				}
			}
			
			function changePolarityByIndex(rowIndex){
				if(rowIndex < 0)
					return;

				var text = $('#faketextarea').html();
				text = decodeText(text);
				
				var table = $('#aspect_table').get(0);
				var row = table.rows[rowIndex];

				var aspect = decodeText($(row.cells[0]).html());
				var type = $(row.cells[0]).get(0).getAttribute('type');
				
				var polarityCell = $(row.cells[1]).get(0);
				var select = polarityCell.childNodes[0];
				
				newPolarity = select.options[select.selectedIndex].text;
				
				var aspectOccur = row.cells[0].getAttribute("occurrence");
				var sentimentIndications = [];
				var SIText = [];
				var occurrences = [];
				var buttonTag = '<\button>';
				var sentimentIndication = row.cells[2];
				
				var i, j, child;
				
				for(i = 0; i < sentimentIndication.childNodes.length-1; i++){
					child = sentimentIndication.childNodes[i];

					SIText[i] = decodeText($(child).html()).split('<')[0];
					sentimentIndications[i] =(/*$(child).html().split('<')[0]*/child.getAttribute("real_text"));
					occurrences[i] =(child.getAttribute("occurrence"));
				}
				
				var startIndex = 0, index;
				var color = colors[polarities.indexOf(newPolarity)];
				color = addHexColor(color, -0x33);
				var occur = 0;
				var colorIndex = 0, symbol;
				var tagBeggining = "<font color=";
				var tagEnd = '</font>';
				var offset = 2;
				var i, siOccur;
				
				//alert('sentimentIndications.length: '+sentimentIndications.length);
				for(i = 0; i < sentimentIndications.length; i++){
					sentimentIndication = sentimentIndications[i];
					siOccur = occurrences[i];
					occur = 0;
					startIndex = 0;
					
					text = $('#faketextarea').html();
					text = decodeText(text);
					var parts = [];
					if(SIText[i] == sentimentIndication){
						parts[0] = sentimentIndication;
					}else{
						var start = 0;
					
						while(start < SIText[i].length && sentimentIndication.substring(start, start+1) == SIText[i].substring(start, start+1))
							start++;
						parts[0] = SIText[i];
				
						if(start != 0 && start != SIText[i].length){
							parts[1] = SIText[i].substring(start);
							parts[0] = SIText[i].substring(0, start);
						}
					}
					
					if( (index = findBeginningByOccurence(text, sentimentIndication, siOccur)) > -1) {
						var start = index;
						for(var k = parts.length-1; k >= 0; k--){
						
								if(k == 0)
									index = start+sentimentIndication.indexOf(parts[k]);
								else
									index = start+sentimentIndication.indexOf(parts[k], sentimentIndication.indexOf(parts[k-1])+1);
								
								j = index;
								var ending = text.substring(index-offset);
								if(text.substring(j-1, j) == '>'){
									while(text.substring(j-1, j) != '<')
										j--;
									j--;
								}else{
									ending = '>'+parts[k]+tagEnd+text.substring(index+parts[k].length);
								}
						
								$('#faketextarea').html(text.substring(0, j)+tagBeggining+color+'";'+ending);

								text = $('#faketextarea').html();
								text = decodeText(text);
						}
					}
				}
				
				text = $('#faketextarea').html();
				text = decodeText(text);
				startIndex = 0, index;
				tagBeggining = "<span style=\"background-color: ";
				tagEnd = '</span>';
				offset = 3;
				occur = 0;
				colorIndex = 0, symbol;
				color = colors[polarities.indexOf(newPolarity)];

				color = addHexColor(color, 0x33);
				
				if( (index = findBeginningByOccurence(text, aspect, aspectOccur)) > -1 && type == 0) {
						colorIndex = index-offset;
						
						j = index;
						
						var ending = text.substring(index-offset);
						if(text.substring(j-1, j) == '>' && text.substring(j-7, j) != '</font>'){
							while(text.substring(j-1, j) != '<'){
								j--;
							}						
							j--;
						}else{
								ending = '>'+aspect+tagEnd+text.substring(index+aspect.length);
						}
						
						var newPolarityIndex = polarities.indexOf(newPolarity);
						
						$('#faketextarea').html(text.substring(0, j)+tagBeggining+color+'";'+ending);
				}
			
			}
				
			function getBeginning(){
				element = $('#faketextarea').get(0);//document.getElementById('text1');
				//alert(element);
				var start = -1, end = 0;
				var sel, range, priorRange;
				if (typeof window.getSelection != "undefined") {
					range = window.getSelection().getRangeAt(0);
					priorRange = range.cloneRange();
					priorRange.selectNodeContents(element);
					priorRange.setEnd(range.startContainer, range.startOffset);
					start = priorRange.toString().length;
					end = start + range.toString().length;
				} else if (typeof document.selection != "undefined" &&
						(sel = document.selection).type != "Control") {
					range = sel.createRange();
					priorRange = document.body.createTextRange();
					priorRange.moveToElementText(element);
					priorRange.setEndPoint("EndToStart", range);
					start = priorRange.text.length;
					end = start + range.text.length;
				}

				return start;
			}
				
			function findBeginningByOccurence(text, substring, occur){
				var index = 0, oc = 0, beginning = 0, j, symbol;
				while((index = text.indexOf(substring, beginning)) >= 0){
					j = index;
					while (j > 0 && text.substring(j-1, j) != '<' && text.substring(j-1, j) != '>'){
						j--;
					}
					symbol = text.substring(j-1, j);
					if(symbol == '<'){
						beginning = index+1;
						continue;
					}
				
					beginning = index+1;
					if(oc == occur)
						return index;
					oc++;
				}
				
				return -1;
			}
				
			function removeAllWhiteSpans(){
				var text = $('#faketextarea').html();
				text = decodeText(text);
				var index, start = 0;
				var whiteSpanBegin = '<span style="background-color: white;">', spanEnd ='</span>';
				while((index = text.indexOf(whiteSpanBegin, 0)) > -1){
					spanClosingIndex = text.indexOf(spanEnd, index);
					string = text.substring(0, index)+text.substring(index+whiteSpanBegin.length, spanClosingIndex)+text.substring(spanClosingIndex+spanEnd.length);
					$('#faketextarea').html(string);
					text = $('#faketextarea').html();
					text = decodeText(text);
				}
			}
			
			function removeAllHighlights(){
				var text = $('#faketextarea').html();
				text = decodeText(text);
				
				var index, start = 0;
				var spanBegin = '<span', spanEnd ='</span>';
				while((index = text.indexOf(spanBegin, 0)) > -1){
					spanClosingIndex = text.indexOf(spanEnd, index);
					var j = index+spanBegin.length;
					while(text.substring(j, j+1) != '>')
						j++;
					j++;
					string = text.substring(0, index)+text.substring(j, spanClosingIndex)+text.substring(spanClosingIndex+spanEnd.length);
					$('#faketextarea').html(string);
					text = $('#faketextarea').html();
					text = decodeText(text);
				}
				
				var spanBegin = '<font', spanEnd ='</font>';
				while((index = text.indexOf(spanBegin, 0)) > -1){
					spanClosingIndex = text.indexOf(spanEnd, index);
					var j = index+spanBegin.length;
					while(text.substring(j, j+1) != '>')
						j++;
					j++;
					string = text.substring(0, index)+text.substring(j, spanClosingIndex)+text.substring(spanClosingIndex+spanEnd.length);
					$('#faketextarea').html(string);
					text = $('#faketextarea').html();
					text = decodeText(text);
				}
			}
			
			function processSelection(selection, occur){
				//alert('selection: '+selection+', occur: '+occur);
				if(occur == -1)
					return null;
				
				var text = $('#hidden_text').html();
				text = decodeText(text);
				
				var beginning = 0;
				var length = selection.toString().length;
				var index = findBeginningByOccurence(text, selection.toString(), occur);
				
				removeAllHighlights();
				
				if(index == -1) return null;

				var i = beginning = index;
				var regex = /^[.,\b\s\n\~\!\@\#\$\%\^\&\*\(\)\_\+\=\-\[\]\{\}\;\:\'\"\\\/\<\>\?]+$/;
				
				while(text.substring(i, i+1).match(regex) != null){
					i++;
				}
				
				while(text.substring(i-1, i).match(regex) == null && i > 0){
					i--;
				}
				
				var end = beginning+length;
				var j = beginning+length;

				while(text.substring(j-1, j).match(regex) != null){
					j--;
				}
				
				while(text.substring(j, j+1).match(regex) == null && j < text.length){
					j++;
				}
				
				if(text.substring(i, j).indexOf('"', 0) >= 0)
					return null;
				
				selectText(i, j);
				
				return text.substring(i, j);
			}
			
			function getSINewBeginning(beginning, end){
				var index = [-1, ""];
				var table = $('#aspect_table').get(0);
				var row, type, aspect, aspectCount, aspectStart, aspectEnd, sentimentIndication, occurrence, child;
				
				//won't let an SI wrap another
				for(var i = 1; i < table.rows.length; i++){
					row = $('#aspect_table').get(0).rows[i];
					var SIs = row.cells[2];
					
					for(var j = 0; j < SIs.childNodes.length-1; j++){					
						child = SIs.childNodes[j];
						sentimentIndication = child.getAttribute("real_text");
						occurrence = child.getAttribute("occurrence");
						
						var siStart = findBeginningByOccurence(decodeText($('#hidden_text').html()), sentimentIndication, occurrence);
						
						var siEnd = siStart+sentimentIndication.length;

						if((siStart >= beginning && siStart <= end) || (siStart <= beginning && siStart+sentimentIndication.length >= beginning)){
							return index;
						}
					}
				}
				
				for(var i = 1; i < table.rows.length; i++){
					row = $('#aspect_table').get(0).rows[i];
					type = row.cells[0].getAttribute('type');
					if(type != 0)
						continue;
					
					aspectCount = row.cells[0].getAttribute('occurrence');
					aspect = $(row.cells[0]).html();
					aspect = decodeText(aspect);
					
					aspectStart = findBeginningByOccurence(decodeText($('#hidden_text').html()), aspect, aspectCount);
					
					aspectEnd = aspectStart+aspect.length;
					
					if(aspectStart >= beginning && aspectStart <= end && (aspectStart != beginning || aspectEnd != end)){
						index[0] = aspectStart;
						index[1] = aspect;
						return index;
					}
				}
				
				return index;
			}
			
			function insideAnother(occur, selection, text){
				var beginning = findBeginningByOccurence(text, selection, occur);
				
				if(beginning == -1)
					return true;
					
				
				var openIndex, closeIndex;
				var startIndex = 0;
				var index;
				
				while((openIndex = text.indexOf('<span', startIndex)) > -1){
					closeIndex = text.indexOf('</span>', openIndex);
					
					if(openIndex < beginning && closeIndex > beginning && (text.substring(beginning-1, beginning) != '>' || text.substring(beginning+selection.length, beginning+selection.length+1) != '<')){
						return true;
					}else if(/*isAspect==1 &&*/openIndex > beginning && closeIndex < beginning+selection.length){
						return true;
					}
					
					startIndex = openIndex+1;
				}
				
				startIndex = 0;
				
				while((openIndex = text.indexOf('<font', startIndex)) > -1){
					closeIndex = text.indexOf('</font>', openIndex);

					if(openIndex < beginning && closeIndex > beginning && (text.substring(beginning-1, beginning) != '>' || text.substring(beginning+selection.length, beginning+selection.length+1) != '<')){
						return true;
					}else if(/*isAspect == 1 &&*/ openIndex > beginning && closeIndex < beginning+selection.length){
						return true;
					}
					
					startIndex = openIndex+1;
				}
				
				return false;
			}
			
			function testSelectionValidity(parent){
				
				while(parent){
					if(parent.getAttribute)
						if(parent.getAttribute('id'))
							if(parent.getAttribute('id')=='faketextarea')
								return true;
					parent = parent.parentNode;
				}
				return false;
			}
			
			function addRowFromSelection(selection, polarity) {	
				var txt;
				var parent;
				
				parent = getSelectionParentElement(selection);
				
				txt = processSelection(selection, getOccurrenceNumber(selection.toString(), getBeginning()));
					
				selection = window.getSelection();
					
				if(txt == null || txt.length == 0){
					alert('Invalid selection');
					return;
				}
				
				//var color;
				if(!polarity || polarity.length == 0 || polarities.indexOf(polarity) < 0){
					polarity = polarities[0];
				}
				
				//color = colors[polarities.indexOf(polarity)];
				var beginning = getBeginning();
				var end = selection.focusOffset;

				appendRow(txt, polarity, beginning, 0);
			}
			
			//type: 0 for normal aspect, 1 for hidden aspect, 2 for general/generic
			function addRow(type) {	
				var selection, txt;
				var parent;
				var annotedText = $('#faketextarea').html();
				annotedText = decodeText(annotedText);
				if(type == 0){
					parent = getSelectionParentElement(selection);
					selection = window.getSelection();

					if(selection.toString().length == 0 || !testSelectionValidity(parent)){
						alert('No text selected');
						return;
					}
					
					txt = processSelection(selection, getOccurrenceNumber(selection.toString(), getBeginning()));
					
					selection = window.getSelection();

					if(insideAnother(getOccurrenceNumber(txt, getBeginning()), txt, annotedText)){
						alert('You can not select this part of the text because it is part of an aspect or sentiment word.');
						restoreHighlights();
						return;
					}
					
					if(txt == null || txt.length == 0){
						alert('Invalid selection');
						restoreHighlights();
						return;
					}
				}
				var option = $("#polarities_dd option:selected")
				var polarity = option.text();
				//var color;
				if(polarity.length == 0)//{
					polarity = polarities[0];
					//color = colors[0];
				//}else
					//var color = option.val();
				
				if(type == 1){
					txt = 'HIDDEN';
					polarity = polarities[0];
					//color = colors[0];
				}else if(type == 2){
					txt = 'GENERAL';
					polarity = polarities[0];
					//color = colors[0];
				}

				var beginning = 0;
				var end = 0;
				if(type == 0){
					beginning = getBeginning();
					end = selection.focusOffset;
				}

				if (type == 0) {
					if(getRowIndex(txt, polarity, getOccurrenceNumber(txt, beginning)) >= 0){
						alert('This aspect has already been added. Try adding sentiment words to the existing one.');
						restoreHighlights();
						return;
					}
				}

				appendRow(txt, polarity, beginning, type);
				//if(type == 0)highlight('backColor', color);
				restoreHighlights();
			  }
		</script>
		
    </head>
    <body style='background-color:white;'>   
		<?php showAlert(); ?>
		<?php if (login_check($mysqli) == true ) : ?>
			<header>
				<nav class="navbar navbar-default">
					<div class="container">
						<div class="navbar-header navbar-left">
							<a class="navbar-brand" href="index.php">RotuLabic</a>
						</div>
						<p class="navbar-text">
							--  Hello, <?php echo htmlentities($_SESSION['username']); ?>!
						</p>
						<div id="navbar" class="collapse navbar-collapse navbar-right">
							<ul class="nav navbar-nav">
								<li><a href="profile.php">Profile</a></li>
								<?php if (($_SESSION['user_role'] == 'processAdmin')  ){
										echo 	'<li><a href="helpAdmin.php">Admin Help</a></li>
												<li><a href="help.php">User Help</a></li>';
									}else{
										echo '<li><a href="help.php">Help</a></li>';
									}
								?>
								<li><a href="includes/logout.php">Logout</a></li>
							</ul>
						</div><!--/.nav-collapse -->
					</div>
				</nav>
			</header>
			
			<h2 align="center">
				<?php echo "Labeling Process : ". $_SESSION['cur_lpName']?>
			</h2>
			<?php showProgressBar()?>
			<h3 align="center">
				<?php echo "Document : " . stripslashes(($docName))?>
			</h3>
			
				<p align="center" name="text1" id="faketextarea" rows="10" cols="80"><?php echo stripslashes($docText)?></p>
				<!--<textarea id="text1" rows="10" cols="80"  readonly ><?php #echo stripslashes($docText)?></textarea>-->
			
				
			<form action="<?php echo esc_url($_SERVER['PHP_SELF']); ?>" 
			method ="post" id="labelingForm" name="labelingForm" >
				
				<fieldset>
					<legend>Add Aspect</legend>
					<div align="center">
					    Select part of the text, then choose the polarity<br>
						<form>
						<select id="polarities_dd" size=<?php echo count(getLabelOptions($mysqli))?>>
						  <?php showColoredLabels($mysqli)?>
						</select>
						<form>
					</div>
				</fieldset>
				
				<div align="center">
					<?php if(isHiddenAspectEnabled($mysqli)) : ?>
						<button id="implicitButton" type='button' class='btn btn-default'>Add Hidden Aspect</button>
					<?php endif; ?>
					<?php if(isGeneralAspectEnabled($mysqli)) : ?>
						<button id="generalButton" type='button' class='btn btn-default'>Add General Aspect</button>
					<?php endif; ?>
				</div><br>
				<div align="center">
					<button id="removeAll" type='button' class='btn btn-default'>Remove All Aspects</button>
				</div><br>
				
				<div align="center">
					<table id='aspect_table'>
					  <tr>
						<th>Aspect</th>
						<th>Polarity</th>
						<th>Sentiment Words</th> 
					  </tr>
					</table>
				<div><br>
				
				<div align="center">
					<input type="button" class='btn btn-default' onclick="validateForm('back')" value="Previous Document">
					<input type="button" class='btn btn-default' onclick="validateForm('jump')" value="Skip Document">
					<?php showButtonNext() ?>					
				</div>
				
				<div align="center" style="padding-top:10px" >
					<input type="button" class='btn btn-default btn-sm' 
					<?php echo "onclick=\"location.href='guideline.php?lpID=" . (string)$_SESSION['cur_lpID'] . "';\"" ?>				 
					value="Return to the instructions" />
				</div>
			</form>
		<?php else : ?>
            <p>
                <span class="error">You are not authorized to access this page.</span> 
				You should try <a href="index.php">logging in</a> first.
            </p>
        <?php endif; ?>
		<footer class="footer">
			<div class="container">
				<p class="text-muted">
					This is a project from <a xmlns:cc="http://creativecommons.org/ns#" href="http://labic.icmc.usp.br/" property="cc:attributionName" rel="cc:attributionURL">LABIC | ICMC-USP</a> 
					and it is licensed by <a rel="license" href="http://creativecommons.org/licenses/by/4.0/">Creative Commons Atribuição 4.0 Internacional</a>.				
					<a rel="license" href="http://creativecommons.org/licenses/by/4.0/"><img alt="Licença Creative Commons" style="border-width:0" src="https://i.creativecommons.org/l/by/4.0/80x15.png" /></a>
				</p>
			</div>
		</footer>	
		<p id='hidden_text' style='visibility: hidden'><?php echo stripslashes($docText)?></p>
		<script>loadPreviousAnnotations(); addSuggestions();</script>
    </body>
</html>
