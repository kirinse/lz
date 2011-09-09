<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
    <title>Livezilla GeoTracking</title>
	<STYLE type="text/css">*{font-family:verdana,arial;font-size:11px;color:#616161;}DIV{font-size:11px;color:#8d8d8d;vertical-align:middle;text-align:center;}</STYLE>
    <script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<!--key-->" type="text/javascript"></script>
    <script type="text/javascript">
	
	var level = 1;
	var map;
	var maptype = 0;
	var selected = null;
	var visitors = Array();
	var loaded = false;
	var preselect = null;
	var default_lat = <!--dlat-->;
	var default_lng = <!--dlng-->;
	var default_zom = <!--dzom-->;
	var imageVisitor = "./images/geo/default/visitor.png";
	var imageVisitorSelected = "./images/geo/default/visitor_selected.png";
	var imageVisitorChat = "./images/geo/default/visitor_chat.png";
	var imageVisitorSelectedChat = "./images/geo/default/visitor_selected_chat.png";
	var shadow = true;
	
	window.onresize = Resize;
	
	function ChangeImageSource(_imageVisitor,_imageVisitorSelected,_imageVisitorChat,_imageVisitorSelectedChat,_shadow)
	{
		imageVisitor = _imageVisitor;
		imageVisitorSelected = _imageVisitorSelected;
		imageVisitorChat = _imageVisitorChat;
		imageVisitorSelectedChat = _imageVisitorSelectedChat;
		shadow = _shadow;
	}
	
	function Resize()
	{  
	    if(document.documentElement.clientHeight >= 14)
		    document.getElementById('map_canvas').style.height = document.documentElement.clientHeight-14;
		if(document.documentElement.clientWidth >= 14)
		    document.getElementById('map_canvas').style.width = document.documentElement.clientWidth-14;
		
		if(loaded)
			ResetView(level);
	}
	
	function ResetView(_level)
	{
		map.setCenter(new GLatLng(default_lat, default_lng), level = _level);
	}
	
	function DoZoomTo(_to,_selected)
	{
		level=parseInt(_to);
		if(_selected && selected != null)
			map.setCenter(selected.GetMarker().getLatLng(), level);
		else
		{
			map.setZoom(level);
		}
	}
	
	function SetMapType(_type)
	{
		_type=maptype=parseInt(_type);
		if(loaded)
		{
			if(_type == 0)
				map.setMapType(G_PHYSICAL_MAP); 
			else if(_type == 1)
				map.setMapType(G_NORMAL_MAP); 
			else if(_type == 2)
				map.setMapType(G_SATELLITE_MAP); 
			else if(_type == 3)
				map.setMapType(G_HYBRID_MAP);
		}
	}
	
	function Initialize() 
	{
		document.getElementById("map_canvas").style.display = "block";
	    Resize();
		if (typeof(GBrowserIsCompatible) != 'undefined' && GBrowserIsCompatible()) 
		{
			map = new GMap2(document.getElementById("map_canvas"));
			document.getElementById('map_canvas').style.background = "#99B3CC";
			
			ResetView(default_zom);
			
			for(var i = 0;i < visitors.length; i++)
			{
				map.addOverlay(visitors[i].GetMarker());
				visitors[i].ShowImage();
			}	
				
			map.disableDoubleClickZoom();	
			GEvent.addListener(map, "zoomend", function(_old,_new) { level = _new; }); 
			loaded = true;			
			SetMapType(maptype);
			if(preselect != null)
				SetSelection(preselect,false);
		}
	}
	
	function ClearAll()
	{		
		map.clearOverlays();
		visitors.length = 0;
	}
	
	function SetSelection(_id,_center)
	{
		preselect = _id;
		oldselected = selected;
		for(var i = 0;i < visitors.length; i++)
		{
			if(visitors[i].GetId() == _id)
			{
				if(_center && loaded)
					map.setCenter(visitors[i].GetMarker().getLatLng(),level);
					
				selected = visitors[i];
				
				if(loaded)
					visitors[i].SetSelection(true);
			}
			else if(loaded && visitors[i] == oldselected)
				visitors[i].SetSelection(false);
		}
	}
	
	function GetSelection(_id,_center)
	{
		return selected.GetId();
	}
	
	function GetZoom()
	{
		return map.getZoom();
	}
	
	function GetLat()
	{
		return map.getCenter().lat();
	}
	
	function GetLong()
	{
		return map.getCenter().lng();
	}
	
	function SetChat(_id,_chat)
	{
		for(var i = 0;i < visitors.length; i++)
		{
			if(visitors[i].GetId() == _id)
			{
				visitors[i].SetChat(_chat); 
				return;
			}
		}
	}
	
	function RemoveVisitor(_id)
	{						
		var new_visitors = Array();
		for(var i = 0;i < visitors.length; i++)
		{
			if(visitors[i].GetId() == _id)
			{	
				visitors[i].SetSelection(false);
				map.removeOverlay(visitors[i].GetMarker());
			}
			else
				new_visitors.push(visitors[i]);
		}
		visitors = new_visitors;
	}
	
	function AddVisitor(_lat,_lng,_id)
	{
		for(var i = 0; i < visitors.length;i++)
		{
			if(visitors[i].GetId() == _id)
				return visitors[i].GetMarker().getLatLng();
		
			if(visitors[i].GetMarker().getLatLng().lat() == _lat && visitors[i].GetMarker().getLatLng().lng() == _lng)
			{
				_lat += (Math.random()+0.2)/500;
				_lng += (Math.random()+0.2)/500;
				return AddVisitor(_lat,_lng,_id);
			}
		}
		
		var point = new GLatLng(_lat,_lng);
  		var letteredIcon = (shadow) ? new GIcon(G_DEFAULT_ICON) : new GIcon();
        letteredIcon.image = imageVisitor;
		letteredIcon.iconSize = new GSize(24, 50);
		letteredIcon.shadowSize = new GSize(38, 49);
		letteredIcon.iconAnchor = new GPoint(10, 50);
        var marker = new GMarker(point, { icon:letteredIcon });
		var visitor = new Visitor(_id,marker);
		
		if(map != null)
		{
			map.addOverlay(marker);
			visitor.ShowImage();
		}
	
		visitors.push(visitor);	
		GEvent.addListener(marker,"click",function(){SetSelection(visitor.GetId(),false);document.body.fireEvent("ondragover", document.createEventObject());});
	}
	
	function AvoidReload()
	{
		event.keyCode=70;
	}

	function UnloadMap()
	{
		if(loaded)
			GUnload();
	}
	
	function Visitor(_id,_marker)
	{
		this.m_Id = _id;
		this.m_Selected = false;
		this.m_Chat = false;
		this.m_Marker =_marker;
		
		this.GetMarker = getMarker;
		this.GetId = getId;
		this.SetSelection = setSelection;
		this.SetChat = setChat;
		this.ShowImage = showImage;
		
		function getMarker()
		{
			return this.m_Marker;
		}
		
		function getId()
		{
			return this.m_Id;
		}
		
		function showImage()
		{
			if(this.m_Selected && this.m_Chat)
				this.m_Marker.setImage(imageVisitorSelectedChat); 
			else if(this.m_Selected && !this.m_Chat)
				this.m_Marker.setImage(imageVisitorSelected); 
			else if(this.m_Chat)
				this.m_Marker.setImage(imageVisitorChat); 
			else
				this.m_Marker.setImage(imageVisitor);
		}
		
		function setSelection(_selected)
		{
			this.m_Selected = _selected;
			this.ShowImage();
		}
		
		function setChat(_chat)
		{
			this.m_Chat = _chat;
			this.ShowImage();
		}
	}
    </script>
  </head>
  <body onload="Initialize()" onunload="UnloadMap()" onkeydown="AvoidReload()" oncontextmenu="return false;" topmargin="0" leftmargin="0">
  <noscript><div align="center"><br />Please activate JavaScript in your Internet<br />Explorer&reg; installation to show the map</div></noscript>
  <div id="map_canvas" style="border:1px solid;height:300px;margin:6px;margin-top:7px;display:none;"><br />Please check your internet connection and make sure<br />that you have entered a valid Google Maps&reg; API key!</div>
  </body>
</html>
