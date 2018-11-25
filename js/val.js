/*!
 *
 *  Form Validation Tool
 *
 */

function Validate(wrapper, rules)
{
    /**
     *    Validate Class.
     */

    this.wrapper = ((typeof wrapper == 'undefined') ? 'form' : wrapper);
    this.target = '/ajax/validate.php';
    this.errorPrepend = 'Validate.';
    this.args = {};

    /** Default types: selector to process **/
    this.types = [
        {type: 'password', selector: '[type="password"]'}, 
        {type: 'email', selector: '[name*="email"]'}, 
        {type: 'phone', selector: '[name*="phone"]'}    
    ]

    /** Default name type rules **/
    var rules = {
        password: {
            minLength: 8, 
            maxLength: 255,
            disallowReg: '^[0]{8}$',
            match: true
        },
        email: {
            requiredReg: '[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}',
            maxLength: 255
        }, 
        phone: {
            minLength: 8,
            maxLength: 24,
        }
    }

    this.rules = function(type)
    {
        /**
         *  Function to return rule set for specific type. Rules are static on prototype init.
         */

        return rules[type];
    }

    this.refresh = function()
    {
        location.reload(false);
    }

    this.clear = function()
    {
        setTimeout( function(){
            clear_status_messages();
        }, 4000);
    }
}

Validate.prototype.validate = function(type, values, rule)
{
    /**
     *  Checks the values against the rules.
     *  Rules must match the default formatting.
     */

    var errorPrepend = this.errorPrepend + "validate(" + type + "): ";
    var invalidCount = 0;
    var test = null;

    if (typeof rule['minLength'] != 'undefined') {
        test = rule['minLength'];
        for (var j = 0; j < values.length; j++) {
            if (values[j].length < test) {
                console.log(errorPrepend + "type: " + type + ", minLength: invalid");
                invalidCount++;
            }
        }
    }

    if (typeof rule['maxLength'] != 'undefined') {
        test = rule['maxLength'];
        for (var j = 0; j < values.length; j++) {
            if (values[j].length > test) {
                console.log(errorPrepend + "type: " + type + ", maxLength: invalid");
                invalidCount++;
            }
        }
    }

    if (typeof rule['disallowReg'] != 'undefined') {
        test = new RegExp(rule['disallowReg']);
        for (var j = 0; j < values.length; j++) {
            if (test.test(values[j])) {
                console.log(errorPrepend + "type: " + type + ", disallowReg: invalid");
                invalidCount++;
            }
        }
    }

    if (typeof rule['requiredReg'] != 'undefined') {
        test = new RegExp(rule['requiredReg']);
        for (var j = 0; j < values.length; j++) {
            if (!test.test(values[j])) {
                console.log(errorPrepend + "type: " + type + ", requiredReg: invalid");
                invalidCount++;
            }
        }
    }

    if (typeof rule['match'] != 'undefined') {
        test = values[0];
        for (var j = 0; j < values.length; j++) {
            if (values[j] != test) {
                console.log(errorPrepend + "type: " + type + ", match: invalid");
                invalidCount++;
            }
        }
    }

    if (invalidCount > 0) {
        return false;
    } else {
        return true;
    }
}

Validate.prototype.toggleStyle = function(valid, elements, input)
{
    /**
     *  Toggles the error input styling.
     */

    if (!valid) {
        if (!elements.hasClass('input-error')) {      
            $(this.wrapper).find(input)
                .addClass('input-error');
        }
    } else {
        if (elements.hasClass('input-error')) {      
            $(this.wrapper).find(input)
                .removeClass('input-error');
        }
    }
}

Validate.prototype.test = function(type, selector)
{
    /**
     *  Tests an input against the rules
     */

    var rule = this.rules(type);
    var elements = $(this.wrapper).find(selector)
    var values = [];

    for (var i = 0; i < elements.length; i++) {
        var element = elements[i];
        values.push(element.value);
    }

    var valid = this.validate(type, values, rule);

    this.toggleStyle(valid, elements, selector);

    return valid;    
}

Validate.prototype.password = function()
{
    /**
     *  Wrapper to test password rules.
     */

    return this.test('password', '[type="password"]');
}

Validate.prototype.email = function()
{
    /**
     *  Wrapper to test email rules.
     */

    return this.test('email', '[name*="email"]');
}

Validate.prototype.attachToForm = function(initRun, wrapper, types)
{
    /**
     *  Attaches this to the wrapper/form change event.
     *  Will test the rules every time the form is updated.
     */

    /** Sync the prototype variables **/
    this.wrapper = ((typeof wrapper != 'undefined') ? wrapper: this.wrapper);
    this.types = ((typeof types != 'undefined') ? types: this.types);

    /** Function process variable **/
    var initRun = ((typeof initRun != 'undefined') ? initRun: false);

    /** Anchor & function variables **/
    var errorPrepend = this.errorPrepend + 'attachToForm(wrapper, types): ';
    var anchor = this;
    var results = [];

    /** Run once on attach **/
    if (initRun) {    
        for (var i = 0; i < anchor.types.length; i++) {
            var result = anchor.test(anchor.types[i].type, anchor.types[i].selector);
            results.push({type: anchor.types[i].type, selector: anchor.types[i].selector, result: result});
        }
    }

    /** Attach to the change event **/
    $(this.wrapper).change(function() {
        console.log(errorPrepend + "Wrapper has changed. Testing known form inputs.");

        for (var i = 0; i < anchor.types.length; i++) {
            var result = anchor.test(anchor.types[i].type, anchor.types[i].selector);
            results.push({type: anchor.types[i].type, selector: anchor.types[i].selector, result: result});
        }
    })


    return results;
}