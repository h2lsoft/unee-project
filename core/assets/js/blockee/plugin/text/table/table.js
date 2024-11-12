class BlockeePlugin__table {

	static focused_element = null;

	static info(){
		return {
			name: 'Table',
			title: "Table",
			keywords: "",
			settings:  true
		}
	}

	static insert(open_settings=true) {

		let contents = `<div data-blockee-type="table" class="blockee-editor-block-element blockee-editor-block-element--table">
			<table class="table w-auto">
				<thead>
				<tr>
					<th contenteditable="true" onclick="BlockeePlugin__table.setFocused(this)">column</th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td contenteditable="true" onclick="BlockeePlugin__table.setFocused(this)">text</td>
				</tr>
				</tbody>
			</table>

</div>`;
		blockeeEditor.blockInsert('table', contents, true);
	}

	static settingsRender()
	{
		let $node = blockeeEditor.blockGetNode();
		let contents = $node.html();

		$node = $node.find('table');

		// add Th, TD contenteditable
		let temp_element = $('<div>').html(contents);
		temp_element.find('caption').remove();
		temp_element.find('td, th').attr({
			contenteditable: "true",
			onclick: "BlockeePlugin__table.setFocused(this)"
		});
		contents = temp_element.html();


		const text_column = blockeeEditor.i18n('column');
		const text_row = blockeeEditor.i18n('row');
		const text_align_left = blockeeEditor.i18n('align_left');
		const text_align_center = blockeeEditor.i18n('align_center');
		const text_align_right = blockeeEditor.i18n('align_right');
		const text_paste_table = blockeeEditor.i18n('paste_table');

		// let form = <textarea autofocus>${contents}</textarea>;
		let form = `
			<div style="margin-bottom: 20px">
			
				<button class="table-btn-column-add" type="button" onclick="BlockeePlugin__table.columnAdd()">
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16"><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/></svg>
					${text_column}
				</button>
				
				<button class="table-btn-column-delete" type="button" onclick="BlockeePlugin__table.columnDelete()">
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-dash" viewBox="0 0 16 16"><path d="M4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8"/></svg>
					${text_column}
				</button>
								
				<button class="table-btn-row-add" type="button" onclick="BlockeePlugin__table.rowAdd()">
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16"><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/></svg> 
					${text_row}
				</button>
				
				<button class="table-btn-row-delete" type="button" onclick="BlockeePlugin__table.rowDelete()">
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-dash" viewBox="0 0 16 16"><path d="M4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8"/></svg>					
					${text_row}
				</button>
				
				<button class="table-btn-align-left" type="button" onclick="BlockeePlugin__table.align('left')" style="width: 40px; text-align: center" title="${text_align_left}">
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-text-left" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M2 12.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m0-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5m0-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m0-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5"/></svg>				
				</button>
				
				<button class="table-btn-align-center" type="button" onclick="BlockeePlugin__table.align('center')" style="width: 40px; text-align: center" title="${text_align_center}">
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-text-center" viewBox="0 0 16 16">
  					<path fill-rule="evenodd" d="M4 12.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5m2-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-2-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5"/></svg>
				</button>
				
				<button class="table-btn-align-right" type="button" onclick="BlockeePlugin__table.align('right')" style="width: 40px; text-align: center" title="${text_align_right}">
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-text-right" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M6 12.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-4-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5m4-3a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7a.5.5 0 0 1-.5-.5m-4-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5"/></svg>
				</button>

				<button class="table-btn-paste-all" type="button" onclick="BlockeePlugin__table.pasteTab()" style="width: 40px; text-align: center" title="${text_paste_table}">
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clipboard-check" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M10.854 7.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708 0"/><path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1z"/><path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0z"/></svg>
				</button>
				
			</div>

${contents}

`;


		let table_caption = $node.find('caption').text() ?? '';
		let table_id = $node.attr('id') ?? '';
		let table_class = $node.attr('class') ?? 'table w-auto';
		let table_style = $node.attr('style') ?? '';

		let render =
			{
				tab_advanced: true,
				tabs:[
					{
						title: 'EDITOR',
						contents: form
					},
					{
						title: 'TABLE',
						contents: `
										<div class="blockee-editor-form-row">                                
                                            <div class="blockee-editor-form-label">Caption</div>
                                            <input type="text" name="table_caption" value="${table_caption}">                                                                                                                                
                                       </div>
									  <div class="blockee-editor-form-row">                                
                                            <div class="blockee-editor-form-label">Id</div>
                                            <input type="text" name="table_id" value="${table_id}">                                                                                                                                
                                       </div>
                                       
                                       <div class="blockee-editor-form-row">                                
                                            <div class="blockee-editor-form-label">Class</div>
                                            <input type="text" name="table_class" value="${table_class}">                                                                                                                                
                                       </div>
                                       
                                       <div class="blockee-editor-form-row">                                
                                            <div class="blockee-editor-form-label">Style</div>
                                            <input type="text" name="table_style" value="${table_style}">                                                                                                                                
                                       </div>
						
						`
					}
				]
			};



		return render;
	}

	static settingsValidate()
	{
		let $node = blockeeEditor.blockGetNode();

		// update contents
		let $contents = $('.blockee-editor-window .blockee-editor-tab--content table:eq(0)')[0].outerHTML;
		$contents = $contents.replaceAll('contenteditable="true"', '')
		$contents = $contents.replaceAll('onclick="BlockeePlugin__table.setFocused(this)"', '');
		$node.html($contents);

		// table properties
		let table_caption =  $('.blockee-editor-window--settings input[name="table_caption"]').val();
		let table_style =  $('.blockee-editor-window--settings input[name="table_style"]').val();
		let table_class =  $('.blockee-editor-window--settings input[name="table_class"]').val();
		let table_id =  $('.blockee-editor-window--settings input[name="table_id"]').val();

		$node.find('table').attr("style", table_style);
		$node.find('table').attr("class", table_class);
		$node.find('table').attr("id", table_id);

		// @todo> add table caption
		if(table_caption === '')
		{
			$node.find("caption").remove();
		}
		else
		{
			if(!$node.find("caption").length)
			{
				$node.find('table').prepend(`<caption>${table_caption}</caption>`);
			}
			else
			{
				$node.find('caption').text(table_caption);
			}
		}





	}

	static setFocused(element)
	{
		BlockeePlugin__table.focused_element = element;
	}


	static columnAdd(th_text='column', td_text="text")
	{
		$('.blockee-editor-tab--content.active table').each(function() {

			$(this).find('thead tr').each(function() {
				$(this).append('<th contenteditable="true" onclick="BlockeePlugin__table.setFocused(this)">'+th_text+'</th>');
			});

			$(this).find('tbody tr').each(function() {
				$(this).append('<td contenteditable="true" onclick="BlockeePlugin__table.setFocused(this)">'+td_text+'</td>');
			});

		});
	}

	static columnDelete() {

		if(!BlockeePlugin__table.focused_element) return;
		const columnIndex = BlockeePlugin__table.focused_element.cellIndex;

		let columnCount = 0;
		$('.blockee-editor-tab--content.active table').each(function() {
			columnCount = $(this).find('thead tr').children().length;
		});

		if(columnCount <= 1) return;

		$('.blockee-editor-tab--content.active table').each(function() {

			$(this).find('thead tr').each(function() {
				$(this).children().eq(columnIndex).remove(); // Supprimer la cellule de l'en-tête
			});


			$(this).find('tbody tr').each(function() {
				$(this).children().eq(columnIndex).remove();
			});
		});

		BlockeePlugin__table.focused_element = null;

	}


	static rowAdd() {

		let focused_row = BlockeePlugin__table.focused_element
			? $(BlockeePlugin__table.focused_element).closest('tr')
			: null;

		$('.blockee-editor-tab--content.active table').each(function() {

			const column_count = $(this).find('thead tr').children().length;


			let new_row = '<tr>';
			for (let i = 0; i < column_count; i++) {
				new_row += '<td contenteditable="true" onclick="BlockeePlugin__table.setFocused(this)">text</td>';
			}
			new_row += '</tr>';


			if (focused_row) {
				$(new_row).insertAfter(focused_row);
			} else {
				$(this).find('tbody').append(new_row);
			}
		});

		BlockeePlugin__table.focused_element = null;
	}


	static rowDelete() {

		let focused_row = BlockeePlugin__table.focused_element
			? $(BlockeePlugin__table.focused_element).closest('tr')
			: null;

		$('.blockee-editor-tab--content.active table').each(function() {

			const rows = $(this).find('tbody tr');
			if (rows.length === 0) return;

			if (focused_row) {
				if (focused_row.find('th').length > 0) return;
				focused_row.remove();
			} else {
				rows.last().remove();
			}

		});


		BlockeePlugin__table.focused_element = null;
	}


	static align(alignment) {
		if (BlockeePlugin__table.focused_element) {
			$(BlockeePlugin__table.focused_element).css('text-align', alignment);
		}
	}



	static pasteTab() {
		navigator.clipboard.readText().then(function(clipboard_text) {
			if (!clipboard_text) {
				alert("The clipboard is empty or contains an incompatible format.");
				return;
			}

			const rows = clipboard_text.split("\n");
			const column_count = rows[0].split("\t").length;

			const $table = $('.blockee-editor-tab--content.active table');
			$table.find('thead').empty();
			$table.find('tbody').empty();

			let headers = rows[0].split("\t");
			let columns = '<tr>';
			for(let i = 0; i < column_count; i++)
			{
				columns += '<th contenteditable="true" onclick="BlockeePlugin__table.setFocused(this)">'+headers[i]+'</th>';
			}
			columns += '</tr>';
			$table.find('thead').html(columns);

			// add columns
			let tds = '';
			for(let i = 1; i < rows.length; i++)
			{
				columns = rows[i].split("\t");

				tds += '<tr>';
				for(let j = 0; j < column_count; j++)
					tds += '<td contenteditable="true" onclick="BlockeePlugin__table.setFocused(this)">'+columns[j]+'</td>';
				tds += '</tr>';
			}

			$table.find('tbody').html(tds);


		}).catch(function(error) {
			alert("Error accessing clipboard: " + error);
		});
	}




}