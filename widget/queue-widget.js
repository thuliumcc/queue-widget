ThuliumQueueWidget = function (container) {
    var _element = container;
    var _sourceUrl = _element.getAttribute('data-url') || '';
    var _interval = (parseInt(_element.getAttribute('data-interval')) || 5) * 1000;
    var _queueId = parseInt(_element.getAttribute('data-queue-id'));
    var _self = this;
    var _dummyFunction = function () {
    };

    ThuliumQueueWidget.prototype.fetchQueueStatus = function (parameters) {
        var alwaysCallback = parameters.always || _dummyFunction;
        var successCallBack = parameters.success || _dummyFunction;
        var getXmlHttpRequest = function () {
            return new XMLHttpRequest();
        };
        var getUrlWithParams = function () {
            var paramDelimiter = (_sourceUrl.match(/w*\?w*/)) ? '&' : '?';
            return _sourceUrl + paramDelimiter + "queue_id=" + encodeURIComponent(parameters.queue_id);
        };
        var ajaxRequest = getXmlHttpRequest();
        ajaxRequest.onreadystatechange = function () {
            try {
                if (ajaxRequest.readyState == 4) {
                    if (ajaxRequest.status == 200) {
                        successCallBack.call(_self, JSON.parse(ajaxRequest.responseText));
                    }
                    alwaysCallback.call(_self, null, ajaxRequest);
                }
            } catch (e) {
                alwaysCallback.call(_self, e, ajaxRequest);
            }
        };
        ajaxRequest.open("GET", getUrlWithParams(), true);
        ajaxRequest.timeout = 10000;
        ajaxRequest.send();
    };

    ThuliumQueueWidget.prototype.renderQueueInfo = function (container, queueInfo) {
        var setValueForClass = function (className, value) {
            container.querySelectorAll('.' + className)[0].innerHTML = value;
        };
        setValueForClass('queue-label', queueInfo.queue);
        setValueForClass('queue-approx-wait', queueInfo.approx_wait);
        setValueForClass('queue-waiting-count', queueInfo.count);
    };

    var _refresh = function () {
        _self.fetchQueueStatus(
            {
                queue_id: _queueId,
                success: function (queueInfo) {
                    _self.renderQueueInfo(container, queueInfo);
                },
                always: function () {
                    setTimeout(_refresh, _interval);
                }
            });
    };
    _refresh();
};

window.onload = function () {
    var containers = document.querySelectorAll('.thulium-queue-widget') || [];
    for (var i = 0; i < containers.length; i++) {
        new ThuliumQueueWidget(containers[i]);
    }
};
