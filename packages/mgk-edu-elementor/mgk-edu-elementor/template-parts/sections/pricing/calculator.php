<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<section class="mgk-section mgk-pricing-calculator-section">
    <div class="mgk-shell">
        <div class="mgk-section-head center">
            <h2>Estimate your monthly cost</h2>
            <p>Adjust the inputs to see your range instantly.</p>
        </div>
        <form class="mgk-pricing-calculator" data-mgk-pricing-calculator data-listing-url="<?php echo esc_url( mgk_get_tutor_listing_url() ); ?>" novalidate>
            <div class="mgk-calculator-inputs">
                <fieldset>
                    <legend>Child's level</legend>
                    <label><input type="radio" name="level" value="preschool" data-label="Preschool"><span>Preschool</span></label>
                    <label><input type="radio" name="level" value="p1p4" data-label="P1-P4"><span>P1-P4</span></label>
                    <label><input type="radio" name="level" value="p5p6" data-label="P5-P6" checked><span>P5-P6</span></label>
                    <label><input type="radio" name="level" value="sec" data-label="Sec"><span>Sec</span></label>
                    <label><input type="radio" name="level" value="jcib" data-label="JC/IB"><span>JC/IB</span></label>
                </fieldset>
                <fieldset>
                    <legend>Subject</legend>
                    <label><input type="radio" name="subject" value="English"><span>English</span></label>
                    <label><input type="radio" name="subject" value="Math" checked><span>Math</span></label>
                    <label><input type="radio" name="subject" value="Chinese"><span>Chinese</span></label>
                    <label><input type="radio" name="subject" value="Science"><span>Science</span></label>
                </fieldset>
                <fieldset>
                    <legend>Tutor tier preference</legend>
                    <label><input type="radio" name="tier" value="part_time"><span>Part-time <small>$35-50</small></span></label>
                    <label><input type="radio" name="tier" value="full_time" checked><span>Full-time <small>$45-65</small></span></label>
                    <label><input type="radio" name="tier" value="ex_moe"><span>Ex-MOE <small>$70-90</small></span></label>
                    <label><input type="radio" name="tier" value="premium"><span>Premium <small>$90-120</small></span></label>
                </fieldset>
                <fieldset>
                    <legend>Lesson duration</legend>
                    <label><input type="radio" name="duration" value="1"><span>1 hour</span></label>
                    <label><input type="radio" name="duration" value="1.5" checked><span>1.5 hour</span></label>
                    <label><input type="radio" name="duration" value="2"><span>2 hour</span></label>
                </fieldset>
                <fieldset>
                    <legend>Frequency per week</legend>
                    <label><input type="radio" name="frequency" value="1" checked><span>1x</span></label>
                    <label><input type="radio" name="frequency" value="2"><span>2x</span></label>
                    <label><input type="radio" name="frequency" value="3"><span>3x</span></label>
                    <label><input type="radio" name="frequency" value="0"><span>Custom</span></label>
                </fieldset>
                <fieldset>
                    <legend>Package type</legend>
                    <label><input type="radio" name="package" value="single"><span>Pay-as-you-go</span></label>
                    <label><input type="radio" name="package" value="8" checked><span>8 lessons (-5%)</span></label>
                    <label><input type="radio" name="package" value="16"><span>16 lessons (-10%)</span></label>
                </fieldset>
                <p class="mgk-form-message" data-mgk-pricing-error>Please select a valid frequency before calculating.</p>
            </div>
            <aside class="mgk-calculator-result">
                <p>Your estimated cost</p>
                <h3 data-mgk-price-title>P5-P6 Math · Full-time tutor</h3>
                <span data-mgk-price-subtitle>1.5h x 1 week x 8-lesson package</span>
                <div>
                    <small>Per lesson</small>
                    <strong data-mgk-per-lesson>$67.50 - $92.50</strong>
                    <em data-mgk-hourly>$45-$62/hr x 1.5h x 0.95 package discount</em>
                </div>
                <div>
                    <small>Per month</small>
                    <strong data-mgk-monthly>$270 - $370</strong>
                    <em>Monthly cost based on weekly schedule</em>
                </div>
                <div class="mgk-total-box">
                    <small data-mgk-package-label>Total 8-lesson package</small>
                    <strong data-mgk-total>$540 - $740</strong>
                    <em data-mgk-savings>Valid 3 months · Save $28-$39 vs single</em>
                </div>
                <dl>
                    <div><dt>Trial lesson (-40%)</dt><dd data-mgk-trial>$40</dd></div>
                    <div><dt>Agency fee</dt><dd>$0</dd></div>
                    <div><dt>Lesson log + digest</dt><dd>Free</dd></div>
                </dl>
                <a class="mgk-btn mgk-btn-light" href="<?php echo esc_url( mgk_get_tutor_listing_url( [ 'subject' => 'Math', 'level' => 'Primary' ] ) ); ?>" data-mgk-pricing-search data-mgk-event="pricing_match_clicked">Find Matching Tutors →</a>
            </aside>
        </form>
    </div>
</section>
