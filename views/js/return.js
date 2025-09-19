/**
 * 2025 Channel-support BV
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * You may not use this file except in compliance with the License.
 *
 * @author    Channel Support <info@channel-support.nl>
 * @copyright 2025 Channel-support BV
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

$(document).ready(function (){

    console.log('Document is ready');
    console.log('fetchStatusUrl =', fetchStatusUrl);

    var tries = 0;
    var maxTries = 10;
    var timeout = 3000;
    (function awaitIcepayStatus() {
        var url = new URL(fetchStatusUrl);
        if(url.searchParams.get('failed'))
            return;

        if (tries >= maxTries) {
            var url = new URL(fetchStatusUrl);
            url.searchParams.set('failed', 1);
            window.location.href = url.href;

            return;
        }

        var request = new XMLHttpRequest();

        request.open('GET', fetchStatusUrl, true);

        request.onload = function() {
            if (request.status >= 200 && request.status < 400) {
                try {
                    var data = JSON.parse(request.responseText);
                    if (data.href) {
                        window.location.href = data.href;
                        return;
                    }

                } catch (e) {
                }
            }

            setTimeout(awaitIcepayStatus, timeout);
        };

        request.onerror = function() {
            setTimeout(awaitIcepayStatus, timeout);
        };

        tries++;
        request.send();
    }());
});