var adminExileClass = function() {
    var root = this;
    this.elements = {};
    this.keyTest = new RegExp('^(?:(?![\u0020\u0023-\u0026\u002B\u002C\u002E\u002F\u003A-\u0040\u005B-\u005E\u0060\u007B-\u007E].*$).)*$');
    this.ipTest = new RegExp('(^\s*((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)(/(3[012]|[12]?[0-9]))?)\s*$)|(^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$)','i');
    var construct = function() {
        root.getElements();
        root.setupElements();
        root.bindEvents();
        root.updateKeyUsage();
    }
    this.getElements = function() {
        root.elements.keyInput = document.getElementById('jform_params_key');
        root.elements.keyValueInput = document.getElementById('jform_params_keyvalue');
        root.elements.keyusageSpan = document.querySelector('label[for=jform_params_keyusage]');
        var parent = root.elements.keyusageSpan.closest('div.control-group');
        root.elements.keyusageControl = parent.querySelector('div.controls');
        root.elements.keyusageControl.innerHTML = '';
    }
    this.setupElements = function() {
        root.elements.keyusageControlAnchor = document.createElement('a');
        root.elements.keyusageControlAnchor.href = '#';
        root.elements.keyusageControlAnchor.textContent = '#';
        root.elements.keyusageControlAnchor.setAttribute('target', '_blank');
        root.elements.keyusageControlAnchor.classList.add('form-control-plaintext');
        root.elements.keyusageControl.appendChild(root.elements.keyusageControlAnchor);
    }
    this.bindEvents = function() {
        ['keyInput', 'keyValueInput'].forEach(function(property) {
            root.watchKeyInput(root.elements[property]);
        });
    }
    this.watchKeyInput = function(el) {
        ['change','keyup'].forEach(function(listener) {
            el.addEventListener(listener, function() {
                root.updateKeyUsage();
                if(!root.keyTest.test(el.value)) {
                    el.blur();
                }
            });
        });
    }
    this.updateKeyUsage = function() {
        var key = root.elements.keyInput.value;
        if(!root.keyTest.test(key)) {
            return;
        }
        var keyValue = root.elements.keyValueInput.value;
        if(!root.keyTest.test(keyValue)) {
            return;
        }
        var keyusage = '';
        if (key.length > 0) {
            keyusage = key;
            if (keyValue.length > 0) {
                keyusage += '=' + keyValue;
            }
        }
        var url = Joomla.getOptions('system.paths').baseFull + "?" + keyusage;
        root.elements.keyusageControlAnchor.href = url;
        root.elements.keyusageControlAnchor.textContent = url;
    }
    this.validIPv46 = function(ip) {
        return root.ipTest.test(ip);
    }
    construct();
}
window.addEventListener('DOMContentLoaded', function() {
    window.adminExile = new adminExileClass();
});