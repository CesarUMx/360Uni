var fw;
var Server;
var FancyWebSocket = function (url)
{
    var callbacks = {};
    var ws_url = url;
    var conn;
    var timerId;


    this.bind = function (event_name, callback)
    {
        fw = this;
        callbacks[event_name] = callbacks[event_name] || [];
        callbacks[event_name].push(callback);
        return this;
    };

    this.send = function (event_name, event_data)
    {
        this.conn.send(event_data);
        return this;
    };

    this.connect = function ()
    {
        if (typeof (MozWebSocket) == 'function')
            this.conn = new MozWebSocket(url);
        else
            this.conn = new WebSocket(url);
        this.keepAlive();

        this.conn.onmessage = function (evt)
        {
            dispatch('message', evt.data);
        };

        this.conn.onclose = function () {
            dispatch('close', null)
        }
        this.conn.onopen = function () {
            dispatch('open', null)
        }
    };

    this.disconnect = function ()
    {
        this.conn.close();
        this.cancelKeepAlive();
    };


    this.keepAlive = function () {
        var timeout = 20000;

        if (fw.conn.readyState == fw.conn.OPEN) {
            send("");
        }
        this.timerId = setTimeout(fw.keepAlive, timeout);
    }
    this.cancelKeepAlive = function () {
        if (fw.timerId) {
            clearTimeout(fw.timerId);
        }
    }



    var dispatch = function (event_name, message)
    {

        if (message == null || message == "")//aqui es donde se realiza toda la accion
        {
        }
        else
        {

            try {

                var JSONdata = JSON.parse(message); //parseo la informacion
                JSONdata.params = typeof JSONdata.params === 'object' ? JSONdata.params : new Object();
                if (window[JSONdata.funcion])
                    window[JSONdata.funcion].call(null, JSONdata.params);
            }
            catch (exception) {
                toastr["error"]("Se generó un error en el WebSocket"+exception);
                
                
                
            }
        }
    };
};

function send(text)
{
    Server.send('message', text);
}
$(document).ready(function ()
{
    Server = new FancyWebSocket('wss://wscs.mondragonmexico.edu.mx');
    Server.bind('open', function ()
    {

    });
    Server.bind('close', function (data)
    {

    });
    Server.bind('message', function (payload)
    {
    });
    Server.connect();
});



