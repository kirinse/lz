function lz_group(_id,_amount,_description,_email,_document,_cihidden,_cimandatory,_tihidden,_timandatory)
{
	this.Id = _id;
	this.Amount = _amount;
	this.Description = (_description.length > 0) ? _description : _id;
	this.Email = _email;	
	this.Option = null;
	this.ActiveDocument = _document;
	this.UpdateOption = lz_group_create_option;
	this.Update = lz_group_update;
	this.Changed = false;
	this.UpdateOption();
	this.ChatInputsHidden = _cihidden;
	this.ChatInputsMandatory = _cimandatory;
	this.TicketInputsHidden = _tihidden;
	this.TicketInputsMandatory = _timandatory;

	function lz_group_create_option()
	{
		if(this.Option == null)
		{
			this.Option = this.ActiveDocument.createElement('option');
		}
		this.Option.value = this.Id;
		this.Option.name = this.Email;
		this.Option.group = this;

		if(this.Amount == 0)
		{
			this.Option.style.color = "#808080";
			this.Option.style.background = "#eeeeee";	
			this.Option.label = "  " + this.Description;
			this.Option.text = "  " + this.Description;

			if(!lz_chat_data.NoPreChatMessages)
			{
				this.Option.label += "  (" + lz_chat_data.Language.LanguageLeaveMessageShort + ")";
				this.Option.text += "  (" + lz_chat_data.Language.LanguageLeaveMessageShort + ")";
			}
		}
		else
		{
			this.Option.style.color = "#000000";
			this.Option.style.background = "#f5ffec";
			this.Option.label = "  " + this.Description + " (Online)";
			this.Option.text = "  " + this.Description + " (Online)";
		}
	}
	
	function lz_group_update(_id,_amount,_description,_email)
	{
		if(this.Id != _id || this.Amount != _amount || this.Description != ((_description.length > 0) ? _description : _id) || this.Email != _email)
		{
			this.Id = _id;
			this.Amount = _amount;
			this.Description = (_description.length > 0) ? _description : _id;
			this.Email = _email	
			this.Changed = true;
		}
		else
			this.Changed = false;
	}
}

function lz_group_list(_document,_selectBox)
{
	this.HeaderOnline;
	this.HeaderOffline;
	this.Groups = Array();
	this.SelectBox = _selectBox;
	this.ActiveDocument = _document;
	this.GroupOnline = false;
	this.GroupOffline = null;
	
	this.Add = lz_group_list_add;
	this.Update = lz_group_list_update;
	this.Place = lz_group_list_place_group;
	this.CreateHeader = lz_group_list_create_header;
	this.PlaceGroup = lz_group_list_place_group;
	this.GetGroupById = lz_group_list_get_group_by_id;
	
	function lz_group_list_add(_id,_amount,_description,_email,_cihidden,_cimandatory,_tihidden,_timandatory)
	{
		var existing = false;
		var currentGroup;
		var lastGroup;
		
		for(var i=0;i<this.Groups.length;i++)
		{
			if(this.Groups[i].Id == _id)
			{
				this.Groups[i].Update(_id,_amount,_description,_email);
				currentGroup = this.Groups[i];
				existing = true;
				break;
			}
		}

		if(!existing)
		{
			currentGroup = new lz_group(_id,_amount,_description,_email,this.ActiveDocument,_cihidden,_cimandatory,_tihidden,_timandatory);
			this.Groups.push(currentGroup);
		}
		
		this.PlaceGroup(currentGroup,lastGroup,!existing);
		lastGroup = currentGroup;
	}
	
	function lz_group_list_update(_groups)
	{
	
		if(_groups.length == 0)
		{
			for(var i = 0;i <this.SelectBox.length;i++)
				if(this.SelectBox.options[i] != this.HeaderOffline)
					this.SelectBox.removeChild(this.SelectBox.options[i]);
				
			if(this.HeaderOffline.parentNode != this.SelectBox)	
				this.SelectBox.appendChild(this.HeaderOffline);
		}
		else
		{
			var addedGroups = Array();
			var groups = _groups.split(";");
	
			this.GroupOnline =
			this.GroupOffline = false;
						
			if(this.HeaderOffline && (this.HeaderOffline.parentNode == null || (this.HeaderOffline.parentNode != null && this.HeaderOffline.parentNode != this.SelectBox)))
				this.SelectBox.appendChild(this.HeaderOffline);
			
			for(var i = 0;i <groups.length;i++)
			{	
				contents = groups[i].split(",");
				addedGroups.push(lz_global_base64_decode(contents[0]));
				this.GroupOnline = (this.GroupOnline || lz_global_base64_decode(contents[1]) > 0);
				this.GroupOffline = (this.GroupOffline || lz_global_base64_decode(contents[1]) == 0);
				this.Add(lz_global_utf8_decode(lz_global_base64_decode(contents[0])),lz_global_utf8_decode(lz_global_base64_decode(contents[1])),lz_global_utf8_decode(lz_global_base64_decode(contents[2])),lz_global_utf8_decode(lz_global_base64_decode(contents[3])),eval(lz_global_base64_decode(contents[4])),eval(lz_global_base64_decode(contents[5])),eval(lz_global_base64_decode(contents[6])),eval(lz_global_base64_decode(contents[7])));
			
			}	
				
			if(!this.GroupOnline && this.HeaderOnline.parentNode == this.SelectBox)
				this.SelectBox.removeChild(this.HeaderOnline);
			else if(this.GroupOnline && (this.HeaderOnline.parentNode == null || (this.HeaderOnline.parentNode != null && this.HeaderOnline.parentNode != this.SelectBox)))
				this.SelectBox.insertBefore(this.HeaderOnline,this.SelectBox.childNodes[0]);
			if(!this.GroupOffline && this.HeaderOffline.parentNode == this.SelectBox)
				this.SelectBox.removeChild(this.HeaderOffline);
			if(this.Groups.length > addedGroups.length)
			{
				var existing;
				for(var i = 0;i <this.Groups.length;i++)
				{
					existing = false;
					for(var j = 0;j <addedGroups.length;j++)
					{
						if(addedGroups[j] == this.Groups[i].Id)
							existing = true;
					}
					if(!existing)
					{
						this.Groups[i].Option.parentNode.removeChild(this.Groups[i].Option);
						this.Groups.splice(i,1);
					}
				}
			}	
			lz_chat_change_group(lz_chat_get_frame_object('lz_chat_frame.3.2.login.1.0','lz_chat_form_groups'));
		}
	}
	
	function lz_group_list_place_group(_group,_lastGroup,_new)
	{
		if(_group != null && _group.Option.parentNode != this.SelectBox || _group.Changed)
			if(_group.Amount > 0)
			{
				this.SelectBox.insertBefore(_group.Option,this.HeaderOffline);
			}
			else
			{
				this.SelectBox.appendChild(_group.Option);
			}
				
		_group.UpdateOption();
	}
	
	function lz_group_list_create_header()
	{
		if(this.SelectBox.childNodes.length == 0)
		{
			this.HeaderOnline = this.ActiveDocument.createElement('option');			
			this.SelectBox.appendChild(this.HeaderOnline);
			this.HeaderOnline.text = "Online";
			this.HeaderOnline.style.color = "#FFFFFF";
			this.HeaderOnline.style.background = "#8C8C8C";
			
			this.HeaderOffline = this.ActiveDocument.createElement('option');
			this.SelectBox.appendChild(this.HeaderOffline);
			this.HeaderOffline.text = "Offline";			
			this.HeaderOffline.style.color = "#FFFFFF";
			this.HeaderOffline.style.background = "#8C8C8C";
		}
	}
	
	function lz_group_list_get_group_by_id(_id)
	{
		for(var i = 0;i <this.Groups.length;i++)
			if(this.Groups[i].Id == _id)
				return this.Groups[i];
		return null;
	}
}