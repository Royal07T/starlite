<!-- Add pricing section start  -->
<div class="cr-course-box" wire:init="loadData" wire:key="@this">
    <div class="cr-content-box">
        <h2>{{ __('courses::courses.add_pricing') }}</h2>
        <p>{{ __('courses::courses.pricing_description') }}</p>
    </div>
    <form class="am-themeform" onsubmit="return false;">
        <fieldset>
            <div class="am-themeform__wrap">
                <div class="form-group-wrap">
                    <div class="form-group">
                        <label class="am-label">{{ __('Pricing Model') }}</label>
                        <div class="am-radio-inline">
                            <div class="am-radio">
                                <input wire:model.live="pricing_type" type="radio" id="pricing_course" value="course">
                                <label for="pricing_course">{{ __('Course Price') }}</label>
                            </div>
                            <div class="am-radio">
                                <input wire:model.live="pricing_type" type="radio" id="pricing_session" value="session">
                                <label for="pricing_session">{{ __('Session Price') }}</label>
                            </div>
                        </div>
                    </div>

                    @if($pricing_type == 'course')
                    <div class="form-group">
                        <div class="cr-free_course">
                            <div class="cr-free_discription">
                                <label for="cr-free-course-toggle">{{ __('courses::courses.free_course') }}</label>
                                <p>{{ __('courses::courses.free_course_description') }}</p>
                            </div>
                            <input type="checkbox" wire:click='toggleIsFree' id="cr-free-course-toggle" class="cr-toggle" wire:model='isFree' wire:ignore>
                        </div>
                    </div>
                    @endif

                    @if (!$isFree && $pricing_type == 'course')
                        <div class="form-group">
                            <label class="am-important" for="course-subtitle">{{ __('courses::courses.course_price') }}</label>
                            <div class="form-group-two-wrap cr-discount-wrap">
                                <div class="at-form-group">
                                    <input type="number" wire:model.live.debounce.500ms="price" id="price" placeholder="70">
                                    <i>{{ getCurrencySymbol() }}</i>
                                </div>
                                <div class="cr-allow-discount">
                                    <label for="allow-discount" class="cr-label">{{ __('courses::courses.allow_discount') }} <span class="cr-optional">({{ __('courses::courses.optional') }})</span></label>
                                    <input type="checkbox" wire:click='toggleDiscountAllowed' id="allow-discount" class="cr-toggle" wire:model="discountAllowed">
                                </div>
                            </div>
                            <x-input-error field_name='price' />
                        </div>
                        @if ($discountAllowed)
                            <div class="form-group">
                                <div class="cr-choose_discount">
                                    <div class="cr-free_course">
                                        <div class="cr-free_discription">
                                            <label for="cr-free-course-toggle">{{ __('courses::courses.course_discount') }}</label>
                                            <p>{{ __('courses::courses.set_course_discount') }}</p>
                                        </div>
                                        @if (!empty($final_price))
                                            <strong>
                                                {{ formatAmount($final_price) }}
                                                <span>{{ __('courses::courses.purchase_price') }}</span>
                                            </strong>
                                        @endif
                                    </div>
                                    <div class="cr-discount-table am-payouthistory">
                                        <table class="am-table">
                                            <thead>
                                            <tr>
                                                <th>{{ __('courses::courses.discount_percentage') }}</th>
                                                <th>{{ __('courses::courses.discount_amount') }}</th>
                                                <th>{{ __('courses::courses.purchase_price') }}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach ($discounts as $discountPercentage)
                                                <tr>
                                                    <td data-label="{{ __('courses::courses.discount_percentage') }}">
                                                        <div class="am-radio">
                                                            <input name="discount_percentage" @if ($discountPercentage == $discount)
                                                            checked
                                                            @endif wire:click="updateDiscount({{ $discountPercentage }})" type="radio" id="discount-{{ $discountPercentage }}" value="{{ $discountPercentage }}">
                                                            <label for="discount-{{ $discountPercentage }}">{{ $discountPercentage }}%</label>
                                                        </div>
                                                    </td>
                                                    <td data-label="{{ __('courses::courses.discount_price') }}"><span>${{ number_format(((float)$discountPercentage/100) * (float)$this->price, 2) }}</span></td>
                                                    <td data-label="{{ __('courses::courses.purchase_price') }}"><span>${{ number_format(((1 - ((float)$discountPercentage/100)) * (float)$this->price), 2) }}</span></td>
                                                </tr>
                                            @endforeach
                                            <tr>
                                                <td data-label="{{ __('courses::courses.discount_percentage') }}">
                                                    <div class="cr-input-wrap">
                                                        <div class="am-radio">
                                                            <input name="discount_percentage" @if ($discount == $customDiscount)
                                                            checked
                                                            @endif wire:click="updateCustomDiscount" type="radio" id="discount-6">
                                                            <label for="discount-6"></label>
                                                        </div>
                                                        <input wire:model.live.debounce.500ms='customDiscount' type="text" placeholder="33%">%
                                                    </div>
                                                </td>
                                                <td data-label="{{ __('courses::courses.discount_price') }}"><span>{{ formatAmount(((integer)$customDiscount/100) * (float)$this->price) }}</span></td>
                                                <td data-label="{{ __('courses::courses.purchase_price') }}"><span>{{ formatAmount(((1 - ((integer)$customDiscount/100)) * (float)$this->price)) }}</span></td>
                                            </tr>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                </div>
                
                {{-- Session Pricing Section --}}
                @if($pricing_type == 'session')
                <div class="form-group-wrap" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                    <div class="cr-content-box">
                        <h3>{{ __('Session Pricing') }}</h3>
                        <p>{{ __('Set pricing for session-based booking. If set, students can book individual sessions.') }}</p>
                    </div>
                    
                    <div class="form-group">
                         <label class="am-label" for="session-price">{{ __('Price Per Session') }}</label>
                         <div class="at-form-group">
                             <input type="number" wire:model.live.debounce.500ms="session_price" id="session-price" placeholder="50" class="form-control">
                             <i>{{ getCurrencySymbol() }}</i>
                         </div>
                         <x-input-error field_name='session_price' />
                    </div>

                    <div class="form-group">
                         <label class="am-label" for="min-sessions">{{ __('Minimum Sessions Required') }}</label>
                         <div class="at-form-group">
                             <input type="number" wire:model.live.debounce.500ms="min_sessions" id="min-sessions" placeholder="5" class="form-control">
                         </div>
                         <x-input-error field_name='min_sessions' />
                    </div>
                </div>
                @endif
            </div>

        </fieldset>

        <div class="am-themeform_footer">

            <a href="{{ route('courses.tutor.edit-course', ['tab' => 'media', 'id' => $courseId]) }}" class="am-white-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                <path d="M10.5 4.5L6 9L10.5 13.5" stroke="#585858" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
                {{ __('courses::courses.back') }}
            </a>

            <button wire:click="savePricing" class="am-btn" wire:loading.remove wire:target="savePricing">{{ __('courses::courses.save_continue') }}</button>
            <button class="am-btn am-btn_disable" wire:loading.flex wire:target="savePricing">{{ __('courses::courses.save_continue') }}</button>
        </div>
    </form>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('modules/courses/css/main.css') }}">
@endpush


