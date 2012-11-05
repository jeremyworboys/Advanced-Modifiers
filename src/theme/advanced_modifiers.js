/*!
 * accounting.js v0.3.2
 * Copyright 2011, Joss Crowcroft
 *
 * Freely distributable under the MIT license.
 * Portions of accounting.js are inspired or borrowed from underscore.js
 *
 * Full details and documentation:
 * http://josscrowcroft.github.com/accounting.js/
 */

(function(root, undefined) {

    /* --- Setup --- */

    // Create the local library object, to be exported or referenced globally later
    var lib = {};

    // Current version
    lib.version = '0.3.2';


    /* --- Exposed settings --- */

    // The library's settings configuration object. Contains default parameters for
    // currency and number formatting
    lib.settings = {
        currency: {
            symbol : "$",       // default currency symbol is '$'
            format : "%s%v",    // controls output: %s = symbol, %v = value (can be object, see docs)
            decimal : ".",      // decimal point separator
            thousand : ",",     // thousands separator
            precision : 2,      // decimal places
            grouping : 3        // digit grouping (not implemented yet)
        },
        number: {
            precision : 0,      // default precision on numbers is 0
            grouping : 3,       // digit grouping (not implemented yet)
            thousand : ",",
            decimal : "."
        }
    };


    /* --- Internal Helper Methods --- */

    // Store reference to possibly-available ECMAScript 5 methods for later
    var nativeMap = Array.prototype.map,
        nativeIsArray = Array.isArray,
        toString = Object.prototype.toString;

    /**
     * Tests whether supplied parameter is a string
     * from underscore.js
     */
    function isString(obj) {
        return !!(obj === '' || (obj && obj.charCodeAt && obj.substr));
    }

    /**
     * Tests whether supplied parameter is a string
     * from underscore.js, delegates to ECMA5's native Array.isArray
     */
    function isArray(obj) {
        return nativeIsArray ? nativeIsArray(obj) : toString.call(obj) === '[object Array]';
    }

    /**
     * Tests whether supplied parameter is a true object
     */
    function isObject(obj) {
        return toString.call(obj) === '[object Object]';
    }

    /**
     * Extends an object with a defaults object, similar to underscore's _.defaults
     *
     * Used for abstracting parameter handling from API methods
     */
    function defaults(object, defs) {
        var key;
        object = object || {};
        defs = defs || {};
        // Iterate over object non-prototype properties:
        for (key in defs) {
            if (defs.hasOwnProperty(key)) {
                // Replace values with defaults only if undefined (allow empty/zero values):
                if (object[key] === null) object[key] = defs[key];
            }
        }
        return object;
    }

    /**
     * Implementation of `Array.map()` for iteration loops
     *
     * Returns a new Array as a result of calling `iterator` on each array value.
     * Defers to native Array.map if available
     */
    function map(obj, iterator, context) {
        var results = [], i, j;

        if (!obj) return results;

        // Use native .map method if it exists:
        if (nativeMap && obj.map === nativeMap) return obj.map(iterator, context);

        // Fallback for native .map:
        for (i = 0, j = obj.length; i < j; i++ ) {
            results[i] = iterator.call(context, obj[i], i, obj);
        }
        return results;
    }

    /**
     * Check and normalise the value of precision (must be positive integer)
     */
    function checkPrecision(val, base) {
        val = Math.round(Math.abs(val));
        return isNaN(val)? base : val;
    }


    /**
     * Parses a format string or object and returns format obj for use in rendering
     *
     * `format` is either a string with the default (positive) format, or object
     * containing `pos` (required), `neg` and `zero` values (or a function returning
     * either a string or object)
     *
     * Either string or format.pos must contain "%v" (value) to be valid
     */
    function checkCurrencyFormat(format) {
        var defaults = lib.settings.currency.format;

        // Allow function as format parameter (should return string or object):
        if ( typeof format === "function" ) format = format();

        // Format can be a string, in which case `value` ("%v") must be present:
        if ( isString( format ) && format.match("%v") ) {

            // Create and return positive, negative and zero formats:
            return {
                pos : format,
                neg : format.replace("-", "").replace("%v", "-%v"),
                zero : format
            };

        // If no format, or object is missing valid positive value, use defaults:
        } else if ( !format || !format.pos || !format.pos.match("%v") ) {

            // If defaults is a string, casts it to an object for faster checking next time:
            return ( !isString( defaults ) ) ? defaults : lib.settings.currency.format = {
                pos : defaults,
                neg : defaults.replace("%v", "-%v"),
                zero : defaults
            };

        }
        // Otherwise, assume format was fine:
        return format;
    }


    /* --- API Methods --- */

    /**
     * Takes a string/array of strings, removes all formatting/cruft and returns the raw float value
     * alias: accounting.`parse(string)`
     *
     * Decimal must be included in the regular expression to match floats (default: "."), so if the number
     * uses a non-standard decimal separator, provide it as the second argument.
     *
     * Also matches bracketed negatives (eg. "$ (1.99)" => -1.99)
     *
     * Doesn't throw any errors (`NaN`s become 0) but this may change in future
     */
    var unformat = lib.unformat = lib.parse = function(value, decimal) {
        // Recursively unformat arrays:
        if (isArray(value)) {
            return map(value, function(val) {
                return unformat(val, decimal);
            });
        }

        // Fails silently (need decent errors):
        value = value || 0;

        // Return the value as-is if it's already a number:
        if (typeof value === "number") return value;

        // Default decimal point is "." but could be set to eg. "," in opts:
        decimal = decimal || ".";

         // Build regex to strip out everything except digits, decimal point and minus sign:
        var regex = new RegExp("[^0-9-" + decimal + "]", ["g"]),
            unformatted = parseFloat(
                ("" + value)
                .replace(/\((.*)\)/, "-$1") // replace bracketed values with negatives
                .replace(regex, '')         // strip out any cruft
                .replace(decimal, '.')      // make sure decimal point is standard
            );

        // This will fail silently which may cause trouble, let's wait and see:
        return !isNaN(unformatted) ? unformatted : 0;
    };


    /**
     * Implementation of toFixed() that treats floats more like decimals
     *
     * Fixes binary rounding issues (eg. (0.615).toFixed(2) === "0.61") that present
     * problems for accounting- and finance-related software.
     */
    var toFixed = lib.toFixed = function(value, precision) {
        precision = checkPrecision(precision, lib.settings.number.precision);
        var power = Math.pow(10, precision);

        // Multiply up by precision, round accurately, then divide and use native toFixed():
        return (Math.round(lib.unformat(value) * power) / power).toFixed(precision);
    };


    /**
     * Format a number, with comma-separated thousands and custom precision/decimal places
     *
     * Localise by overriding the precision and thousand / decimal separators
     * 2nd parameter `precision` can be an object matching `settings.number`
     */
    var formatNumber = lib.formatNumber = function(number, precision, thousand, decimal) {
        // Resursively format arrays:
        if (isArray(number)) {
            return map(number, function(val) {
                return formatNumber(val, precision, thousand, decimal);
            });
        }

        // Clean up number:
        number = unformat(number);

        // Build options object from second param (if object) or all params, extending defaults:
        var opts = defaults(
                (isObject(precision) ? precision : {
                    precision : precision,
                    thousand : thousand,
                    decimal : decimal
                }),
                lib.settings.number
            ),

            // Clean up precision
            usePrecision = checkPrecision(opts.precision),

            // Do some calc:
            negative = number < 0 ? "-" : "",
            base = parseInt(toFixed(Math.abs(number || 0), usePrecision), 10) + "",
            mod = base.length > 3 ? base.length % 3 : 0;

        // Format the number:
        return negative + (mod ? base.substr(0, mod) + opts.thousand : "") + base.substr(mod).replace(/(\d{3})(?=\d)/g, "$1" + opts.thousand) + (usePrecision ? opts.decimal + toFixed(Math.abs(number), usePrecision).split('.')[1] : "");
    };


    /**
     * Format a number into currency
     *
     * Usage: accounting.formatMoney(number, symbol, precision, thousandsSep, decimalSep, format)
     * defaults: (0, "$", 2, ",", ".", "%s%v")
     *
     * Localise by overriding the symbol, precision, thousand / decimal separators and format
     * Second param can be an object matching `settings.currency` which is the easiest way.
     *
     * To do: tidy up the parameters
     */
    var formatMoney = lib.formatMoney = function(number, symbol, precision, thousand, decimal, format) {
        // Resursively format arrays:
        if (isArray(number)) {
            return map(number, function(val){
                return formatMoney(val, symbol, precision, thousand, decimal, format);
            });
        }

        // Clean up number:
        number = unformat(number);

        // Build options object from second param (if object) or all params, extending defaults:
        var opts = defaults(
                (isObject(symbol) ? symbol : {
                    symbol : symbol,
                    precision : precision,
                    thousand : thousand,
                    decimal : decimal,
                    format : format
                }),
                lib.settings.currency
            ),

            // Check format (returns object with pos, neg and zero):
            formats = checkCurrencyFormat(opts.format),

            // Choose which format to use for this value:
            useFormat = number > 0 ? formats.pos : number < 0 ? formats.neg : formats.zero;

        // Return with currency symbol added:
        return useFormat.replace('%s', opts.symbol).replace('%v', formatNumber(Math.abs(number), checkPrecision(opts.precision), opts.thousand, opts.decimal));
    };


    /**
     * Format a list of numbers into an accounting column, padding with whitespace
     * to line up currency symbols, thousand separators and decimals places
     *
     * List should be an array of numbers
     * Second parameter can be an object containing keys that match the params
     *
     * Returns array of accouting-formatted number strings of same length
     *
     * NB: `white-space:pre` CSS rule is required on the list container to prevent
     * browsers from collapsing the whitespace in the output strings.
     */
    lib.formatColumn = function(list, symbol, precision, thousand, decimal, format) {
        if (!list) return [];

        // Build options object from second param (if object) or all params, extending defaults:
        var opts = defaults(
                (isObject(symbol) ? symbol : {
                    symbol : symbol,
                    precision : precision,
                    thousand : thousand,
                    decimal : decimal,
                    format : format
                }),
                lib.settings.currency
            ),

            // Check format (returns object with pos, neg and zero), only need pos for now:
            formats = checkCurrencyFormat(opts.format),

            // Whether to pad at start of string or after currency symbol:
            padAfterSymbol = formats.pos.indexOf("%s") < formats.pos.indexOf("%v") ? true : false,

            // Store value for the length of the longest string in the column:
            maxLength = 0,

            // Format the list according to options, store the length of the longest string:
            formatted = map(list, function(val, i) {
                if (isArray(val)) {
                    // Recursively format columns if list is a multi-dimensional array:
                    return lib.formatColumn(val, opts);
                } else {
                    // Clean up the value
                    val = unformat(val);

                    // Choose which format to use for this value (pos, neg or zero):
                    var useFormat = val > 0 ? formats.pos : val < 0 ? formats.neg : formats.zero,

                        // Format this value, push into formatted list and save the length:
                        fVal = useFormat.replace('%s', opts.symbol).replace('%v', formatNumber(Math.abs(val), checkPrecision(opts.precision), opts.thousand, opts.decimal));

                    if (fVal.length > maxLength) maxLength = fVal.length;
                    return fVal;
                }
            });

        // Pad each number in the list and send back the column of numbers:
        return map(formatted, function(val, i) {
            // Only if this is a string (not a nested array, which would have already been padded):
            if (isString(val) && val.length < maxLength) {
                // Depending on symbol position, pad after symbol or at index 0:
                return padAfterSymbol ? val.replace(opts.symbol, opts.symbol+(new Array(maxLength - val.length + 1).join(" "))) : (new Array(maxLength - val.length + 1).join(" ")) + val;
            }
            return val;
        });
    };


    /* --- Module Definition --- */

    // Export accounting for CommonJS. If being loaded as an AMD module, define it as such.
    // Otherwise, just add `accounting` to the global object
    if (typeof exports !== 'undefined') {
        if (typeof module !== 'undefined' && module.exports) {
            exports = module.exports = lib;
        }
        exports.accounting = lib;
    } else if (typeof define === 'function' && define.amd) {
        // Return the library as an AMD module:
        define([], function() {
            return lib;
        });
    } else {
        // Use accounting.noConflict to restore `accounting` back to its original value.
        // Returns a reference to the library's `accounting` object;
        // e.g. `var numbers = accounting.noConflict();`
        lib.noConflict = (function(oldAccounting) {
            return function() {
                // Reset the value of the root's `accounting` variable:
                root.accounting = oldAccounting;
                // Delete the noConflict method:
                lib.noConflict = undefined;
                // Return reference to the library to re-assign it:
                return lib;
            };
        })(root.accounting);

        // Declare `fx` on the root (global/window) object:
        root['accounting'] = lib;
    }

    // Root will be `window` in browser or `global` on the server:
}(this));



/**
 * Advanced Modifiers Javascript
 *
 * @package    advanced_modifiers
 * @author     Jeremy Worboys <jeremy@complexcompulsions.com>
 * @link       http://complexcompulsions.com
 * @copyright  Copyright (c) 2012, Jeremy Worboys
 */

if (window.jQuery && window.ExpressoStore) {
    jQuery(function($) {
        if (ExpressoStore.products) {

            // convert a form into a useful hash (supports radios etc)
            var serializeForm = function(form) {
                var out = {};
                var formArray = $(form).serializeArray();
                $.each(formArray, function(i, elem) {
                    out[elem.name] = elem.value;
                });
                return out;
            };

            // find a sku for the current form state
            var matchSku = function(formdata) {
                // check we have the necessary product data
                var product = ExpressoStore.products[formdata.entry_id];
                if (!product) return false;

                // if there is only one sku, return it
                if (product.stock.length === 1) return product.stock[0];

                // loop through modifiers, and match them to skus
                for (var i=0; i<product.stock.length; i++) {
                    var match = true;
                    // are there any modifiers which don't match this sku?
                    for (var mod_id in product.stock[i]["opt_values"]) {
                        var mod_name = "modifiers["+mod_id+"]";
                        if (formdata[mod_name] != product.stock[i]["opt_values"][mod_id]) {
                            match = false; break;
                        }
                    }
                    // found the correct sku
                    if (match) return product.stock[i];
                }

                return false;
            };

            var evaluateAdvancedModifier = function(adv_mod, mod_map) {
                var mod_val;
                try {
                    var f = new Function("scope", "with(scope) { return (" + adv_mod + "); }");
                    mod_val = parseFloat(f(mod_map));
                }
                catch (e) {
                    console.log(adv_mod, mod_map, e);
                    mod_val = 0;
                }
                return mod_val;
            };

            // calculate the price for the current form state
            var calculatePrice = function(formdata) {
                // check we have the necessary product data
                var product = ExpressoStore.products[formdata.entry_id];
                if (!product) return false;

                // add any applicable modifiers
                var price = product.price,
                    opts = [];
                for (var mod_id in product.modifiers) {
                    var modifier = formdata["modifiers_"+mod_id];
                    var option = product.modifiers[mod_id].options[modifier];
                    if (option) {
                        opts.push(option.product_opt_id);
                        // price += option.opt_price_mod_val;
                    }
                }

                var id = opts.join('-');
                if (product['stock'][0]['advanced_modifiers'][id]) {
                    price = product['stock'][0]['advanced_modifiers'][id];
                }

                return price;
            };

            // update magic product classes
            var updateSku = function() {
                var sku = ""; var stock_level = ""; var in_stock = true;

                // find the currently selected sku
                var formdata = serializeForm(this.form);
                var skudata = matchSku(formdata);
                if (skudata !== false) {
                    sku = skudata.sku;
                    if (skudata.track_stock === "y") {
                        stock_level = skudata.stock_level;
                        if (stock_level <= 0) in_stock = false;
                    }
                }

                // update the classes
                var form = $(this.form);
                $(".store_product_sku", form).val(sku).text(sku).trigger("change");
                $(".store_product_stock", form).val(stock_level).text(stock_level).trigger("change");
                $(".store_product_in_stock", form).toggle(in_stock);
                $(".store_product_out_of_stock", form).toggle(in_stock === false);

                // calculate the current price
                var price = calculatePrice(formdata);
                if (price) {
                    var price_str = ExpressoStore.formatCurrency(price);
                    var price_inc_tax = price * (1 + ExpressoStore.cart["tax_rate"]);
                    var price_inc_tax_str = ExpressoStore.formatCurrency(price_inc_tax);
                    $(".store_product_price_val", form).val(price).text(price).trigger("change");
                    $(".store_product_price", form).val(price_str).html(price_str).trigger("change");
                    $(".store_product_price_inc_tax_val", form).val(price_inc_tax).text(price_inc_tax).trigger("change");
                    $(".store_product_price_inc_tax", form).val(price_inc_tax_str).html(price_inc_tax_str).trigger("change");
                }
            };

            // register form change handler
            $('form.store_product_form')
                .undelegate('[name^="modifiers"]:not(:radio)', 'change')
                .undelegate('[name^="modifiers"]:radio',       'click')
                .delegate('[name^="modifiers"]:not(:radio)', 'change', updateSku)
                .delegate('[name^="modifiers"]:radio',       'click',  updateSku)
                .find("input:first").each(updateSku);
        }
    });
}
