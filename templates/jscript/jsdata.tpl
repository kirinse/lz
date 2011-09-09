function lz_chat_data_box()
{
	this.Id = '';
	this.Language = new lz_chat_data_language();
	this.Templates = new lz_chat_data_templates();
	this.InternalUser = new lz_chat_internal_user();
	this.ExternalUser = new lz_chat_external_user();
	this.Status = new lz_chat_status();
	this.LastSender = -2;
	this.LastSound = 0;
	this.AlternateRow = true;
	this.SetupError = lz_global_utf8_decode(lz_global_base64_decode('<!--setup_error-->'));
	this.FileUpload = new lz_chat_file_upload();
	this.PermittedFrames = 16;
	this.LastConnection = 0;
	this.CurrentApplication = "chat";
	this.ChatFrequency = <!--extern_frequency-->;
	this.PollTimeout = <!--extern_timeout-->;
	this.PollHash = '';
	this.PollAcid = '';
	this.ShoutNeeded = false;
	this.ShoutRunning = false;
	this.ConnectionBroken = false;
	this.ConnectionRunning = false;
	this.LastConnectionFailed = false;
	this.DirectLogin = <!--direct_login-->;
	this.SoundsAvailable = <!--is_ie-->;
	this.IECompatible = <!--is_ie-->;
	this.Groups = null;
	this.TimerTyping = null;
	this.TimerWaiting = null;
	this.GetParameters = '<!--url_get_params-->';
	this.TempImage = new Image();
	this.TimezoneOffset = (new Date().getTimezoneOffset() / 60) * -1;
	this.ActiveAlertFrame = null;
	this.NoPreChatMessages = <!--offline_message_pre_chat-->;
	this.GeoResolution;
	this.QueueMessageAppended = false;
	this.ConnectedMessageAppended = false;
	this.WaitingMessageAppended = false;
	this.WindowUnloaded = false;
	this.WindowNavigating = false;
	this.WindowAnnounce = null;
	this.MessageCount = 1;
	this.SoundObject = null;
	this.InputFieldIndices = new Array('0','1','2','3','4','5','6','7','8','9','111','112','113','114');

	this.SYSTEM = -1;
	this.INTERNAL = 0;
	this.EXTERNAL = 1;
	this.MAXCHATLENGTH = 1500;
	this.STATUS_STOPPED = 4;
	this.STATUS_ACTIVE = 3;
	this.STATUS_ALLOCATED = 2;
	this.STATUS_INIT = 1;
	this.STATUS_START = 0;
	this.FILE_UPLOAD_OVERSIZED = 2;
	this.FILE_UPLOAD_REJECTED = 1;
	this.IMAGE_FILE_UPLOAD_SUCCESS = './images/file_upload_success.gif';
	
	function lz_chat_status()
	{
		this.Status = 0;
		this.Loaded = false;
	}
	
	function lz_chat_data_language()
	{
		this.FillMandatoryFields = "<!--lang_client_fill_mandatory_fields-->";
		this.SelectValidGroup = "<!--lang_client_select_valid_group-->";
		this.LanguageLeaveMessageShort = "<!--lang_client_leave_message_short-->";		
		this.StartChat = "<!--lang_client_start_chat-->";
		this.StartSystem = "<!--lang_client_start_system-->";
		this.ConnectionBroken = "<!--lang_client_con_broken-->";
		this.MessageTooLong = "<!--lang_client_message_too_long-->";
		this.MessageReceived = "<!--lang_client_message_received-->";
		this.MessageFlood = "<!--lang_client_message_flood-->";
		this.RequestPermission = "<!--lang_client_file_upload_requesting-->";
		this.StartUpload = "<!--lang_client_file_upload_send_file-->";
		this.SelectFile = "<!--lang_client_file_upload_select_file-->";
		this.FileProvided = "<!--lang_client_file_upload_provided-->";
		this.WaitForRepresentative = "<!--lang_client_wait_for_representative-->";
		this.RepresentativeLeft = "<!--lang_client_no_representative-->";
		this.SelectRating = "<!--lang_client_please_rate-->";
		this.FileUploadRejected = "<!--lang_client_file_request_rejected-->";
		this.FileUploadOversized = "<!--lang_client_file_upload_oversized-->";
		this.TransmittingFile = "<!--lang_client_transmitting_file-->";
		this.Guest = "<!--lang_client_guest-->";
		this.ClientForwarding = "<!--lang_client_forwarding-->";
		this.ClientInternArrives = "<!--lang_client_intern_arrives-->";
		this.ClientErrorUnavailable = "<!--lang_client_error_unavailable-->";
		this.ClientIntLeft = "<!--lang_client_int_left-->";
		this.ClientIntDeclined = "<!--lang_client_int_declined-->";
		this.ClientStillWaitingInt = "<!--lang_client_still_waiting_int-->";
		this.ClientThankYou = "<!--lang_client_thank_you-->";
		this.ClientIntIsConnected = "<!--lang_client_int_is_connected-->";
		this.ClientNoInternUsers = "<!--lang_client_no_intern_users-->";
		this.ClientNoInternUsersShort = "<!--lang_client_no_intern_users_short-->";
		this.ClientErrorGroups = "<!--lang_client_error_groups-->";
		this.RateSuccess = "<!--lang_client_rate_success-->";
		this.RateMax = "<!--lang_client_rate_max-->";
		this.System = "<!--lang_client_system-->";
		this.QueueMessage = "<!--lang_client_queue_message-->";
		this.NextOperator = "<!--lang_client_queue_next_operator-->";
	}
	
	function lz_chat_data_templates()
	{
		this.MessageInternal = '<!--template_message_intern-->';
		this.MessageExternal = '<!--template_message_extern-->';
		this.MessageAdd = '<!--template_message_add-->';
		this.MessageAddAlt = '<!--template_message_add_alt-->';
	}
	
	function lz_chat_internal_user()
	{
		this.Id = '<!--requested_intern_userid-->';
		this.Fullname = '';
		this.Available = false;
		this.TextAlign = 'left';
		this.ProfilePictureTime = 0;
		this.Language = "en";
	}
	
	function lz_chat_external_user()
	{
		this.Id = '';
		this.Username = '<!--login_value_111-->';
		this.Email = '<!--login_value_112-->';
		this.Company = '<!--login_value_113-->';
		this.Question = '';
		this.CustomFields = new Array(<!--login_value_customs-->);
		this.MailText = '';
		this.Group = '';
		this.Typing = false;
		this.MessagesSent = new Array();
		this.MessagesReceived = new Array();
		this.Session;
		this.TextAlign = 'left';
	}
	
	function lz_chat_file_upload()
	{
		this.Filename;
		this.Running;
		this.Permitted;
		this.Error;
	}
}

function lz_chat_post()
{
	this.MessageText = '';
	this.MessageTranslation = '';
	this.MessageId = '';
	this.MessageTime = 0;
	this.Received = false;
}

