function sfAssetsLibrary_Engine()
{
  // Browser check
  var ua = navigator.userAgent;
  this.isMSIE = (navigator.appName == "Microsoft Internet Explorer");
  this.isGecko = ua.indexOf('Gecko') != -1;
  this.isOpera = ua.indexOf('Opera') != -1;

  // Fake MSIE on Opera and if Opera fakes IE, Gecko or Safari cancel those
  if (this.isOpera) {
    this.isMSIE = true;
    this.isGecko = false;
  }
}

sfAssetsLibrary_Engine.prototype = {
  init : function(url)
  {
    this.url = url;
  },

  fileBrowserReturn : function (url)
  {
    if(this.isTinyMCE)
    {
      tinyMCE.setWindowArg('editor_id', this.fileBrowserWindowArg);
      if (this.fileBrowserType == 'image')
      {
        this.callerWin.showPreviewImage(url);
      }
    }
    this.callerWin.document.forms[this.callerFormName].elements[this.callerFieldName].value = url;
  },
  
  fileBrowserCallBack : function (field_name, url, type, win)
  {
    this.isTinyMCE = true;
    this.fileBrowserWindowArg = tinyMCE.getWindowArg('editor_id');
    var template = new Array();
    template['title']  = 'Assets';
    var url = this.url;
    if (type == 'image')
      url += '/images_only/1';
    template['file']   = url;
    template['width']  = 550;
    template['height'] = 600;
    template['close_previous'] = 'no';

    this.callerWin = win;
    this.callerFormName = 0;
    this.callerFieldName = field_name;
    this.fileBrowserType = type;
    tinyMCE.openWindow(template, {inline : "yes", scrollbars: 'yes'});
  },

  openWindow : function(options)
  {
    var width, height, x, y, resizable, scrollbars, url;
 
    if (!options) return;
    if (!options['field_name']) return;
    if (options['url'])
    {
      this.url = options['url'];
    }
    else if (!this.url)
    {
      return;
    }
    this.callerWin = self;
    this.callerFormName = (options['form_name'] == '') ? 0 : options['form_name'];
    this.callerFieldName = options['field_name'];
    this.fileBrowserType = options['type'];
    url = this.url;
  
    if (options['type'] == 'image') url += '/images_only/1';
    if (!(width = parseInt(options['width']))) width = 1000;
    if (!(height = parseInt(options['height']))) height = 600;

    // Add to height in M$ due to SP2 WHY DON'T YOU GUYS IMPLEMENT innerWidth of windows!!
    if (sfAssetsLibrary.isMSIE)
      height += 40;
    else
      height += 20;

    x = parseInt(screen.width / 2.0) - (width / 2.0);
    y = parseInt(screen.height / 2.0) - (height / 2.0);

    resizable = (options && options['resizable']) ? options['resizable'] : "no";
    scrollbars = (options && options['scrollbars']) ? options['scrollbars'] : "no";

    var modal = (resizable == "yes") ? "no" : "yes";

    if (sfAssetsLibrary.isGecko && sfAssetsLibrary.isMac) modal = "no";

    if (options['close_previous'] != "no") try {sfAssetsLibrary.lastWindow.close();} catch (ex) {}

    var win = window.open(url, "sfPopup" + new Date().getTime(), "top=" + y + ",left=" + x + ",scrollbars=" + scrollbars + ",dialog=" + modal + ",minimizable=" + resizable + ",modal=" + modal + ", width=1000, height=600,resizable=" + resizable);
    this.fileBrowserWin = win;
    if (options['close_previous'] != "no") sfAssetsLibrary.lastWindow = win;

    win.focus();
  }
}

var sfAssetsLibrary = new sfAssetsLibrary_Engine();