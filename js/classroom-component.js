Vue.directive('select', {
    twoWay: true,
    bind: function(el, binding, vnode) {
        $(el).select2().on("select2:select", (e) => {
            el.dispatchEvent(new Event('change', { target: e.target }));
        });
        $(el).select2().on('select2:close', (e) => {
            var value = $(el).val();
            el.dispatchEvent(new Event('change', { target: e.target }));
        });
    },
    update: function(value) {
        $(this.el).val(value).trigger('change');
    },
    unbind: function() {
        $(this.el).off().select2('destroy');
    }
});
Vue.component('el-daterange-picker', {
    props: {
        id: {
            type: String,
            default: this._uid
        },
        name: {
            type: String,
            default: ''
        },
        'custom-class': {
            type: String,
            default: 'form-control'
        },
        value: {
            type: String,
            default: ''
        },
        disabled: {
            type: Boolean,
            default: false
        }
    },
    template: `
    <div>
        <input type="text" ref="picker" :value="value" :id="id" :name="name" :class="customClass" :disabled="disabled">
    </div>`,
    mounted() {
        const thisDateRangeComponent =this;
        const quarter = moment().quarter();
        $(this.$refs.picker).daterangepicker({
            showDropdowns: true,
            autoUpdateInput: false,
            opens: 'right',
            locale: {
                cancelLabel: 'Show all',
                applyLabel: 'Ok',
                format: 'DD/MM/YYYY',
            },
            ranges: {
                'Today': [moment()],
                'This week': [moment().startOf('week'), moment().endOf('week')],
                'This month': [moment().startOf('month'), moment().endOf('month')],
                'This quarter': [moment().quarter(quarter).startOf('quarter'), moment().quarter(quarter).endOf('quarter')],
                'This year': [moment().startOf('year'), moment().endOf('year')],
                'Last week': [moment().subtract(1, 'week').startOf('week'), moment().subtract(1, 'week').endOf('week')],
                'Last month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                'Last quarter': [moment().subtract(1, 'quarter').startOf('quarter'), moment().subtract(1, 'quarter').endOf('quarter')],
                'Last year': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
                '30 days ago': [moment().subtract(29, 'days'), moment()],
                '60 days ago': [moment().subtract(59, 'days'), moment()],
                '90 days ago': [moment().subtract(89, 'days'), moment()],
                '120 days ago': [moment().subtract(119, 'days'), moment()],
            }
        }, cb(moment().startOf('year').format('DD/MM/YYYY'),moment().endOf('year').format('DD/MM/YYYY')));
        $(this.$refs.picker).on('hide.daterangepicker hideCalendar.daterangepicker ', function(ev, picker) {
            let st = picker.startDate.format('DD/MM/YYYY');
            let ed = picker.endDate.format('DD/MM/YYYY');
            let dt = '';
            if(st == ed) {
                dt = st;
            } else {
                dt = picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY');
            }
            $(this).val(dt);
            thisDateRangeComponent.$emit('update', dt);
        });
        $(this.$refs.picker).on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            thisDateRangeComponent.$emit('update', '');
        });
        $(this.$refs.picker).on('apply.daterangepicker', function(ev, picker) {
            let st = picker.startDate.format('DD/MM/YYYY');
            let ed = picker.endDate.format('DD/MM/YYYY');
            let dt = '';
            if(st == ed) {
                dt = st;
            } else {
                dt = picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY');
            }
            $(this).val(dt);
            thisDateRangeComponent.$emit('update', dt);
        });	
    }
});
Vue.component('summary-form-response', {
    props: {
        questions: Array,
        classroom_id: String
    },
    data() {
        return {
            question_loading: false,
        }
    },
    methods: {
        QuestionType(typeVal) {
            let value = '';
            switch (typeVal) {
                case 'short_answer':
                    value = 'Short Answer';
                    break;
                case 'multiple_choice':
                    value = 'Multiple Choice';
                    break;
                case 'checkbox':
                    value = 'Checkbox';
                    break;
                case 'radio':
                    value = 'Radio';
                    break;
                default:
                    break;
            }
            return value;
        },
        hasChoices(type) {
            return ['multiple_choice', 'checkbox', 'radio'].includes(type);
        },
    },
    template: `
    <div>
        <div class="questions-list">
            <div
                v-for="(question, index) in questions"
                :key="question.question_id"
                class="question-item form-container response-questions"
                v-loading="question_loading"
            >
                <div class="question-type"><h4>{{ QuestionType(question.type) }}</h4></div>
                <div v-if="question.type === 'checkbox'" class="desc-question-type"><span>(Multiple selections allowed)</span></div>
                <div v-if="question.required">
                    <span class="text-danger">* Required</span>
                </div>
                <div v-else>
                    <span class="text-deafault">No required</span>
                </div>
                <div class="question-text d-flex align-item-baseline">
                    <span class="q-label">Q:</span>
                    <div class="question-text"><h4>{{ question.text }}</h4></div>
                </div>
                <div v-if="question.question_id && hasChoices(question.type)" class="question-item-options">
                    <option-answer-response :Question="question" :classroom_id="classroom_id"></option-answer-response>
                </div>
                <div v-if="question.question_id && !hasChoices(question.type)" class="question-item-short-answer">
                    <short-answer-response :Question="question" :classroom_id="classroom_id"></short-answer-response>
                </div>
            </div>
        </div>
    </div>
    `
});
Vue.component('short-answer-response', {
    props: {
        Question: {
            type: Object,
            required: true
        },
        classroom_id: {
            type: String
        }
    },
    data: function () {
        return {
            loading: false,
            store_answer: [],
            allAnswer: 0, 
            cardsPerPage: 5,
            currentPage: 1
        };
    },
    template: `
    <div v-loading="loading" class="el-short-answer-response">
        <div v-if="loading === false">
            <div class="mb-2">
                <span class="count-answer-label">{{ allAnswer }} Response</span>
            </div>
            <div v-if="store_answer.length" :id="'card_list_' + Question.question_id">
                <div v-for="(item, index) in store_answer" :key="index" class="card-item">
                    <div class="card-item-content">
                        <span>{{ item.text }}</span>
                    </div>
                </div>
                <div class="pagination">
                    <button class="btn btn-white btn-flex" @click="prevPage" :disabled="currentPage === 1"><i class="fas fa-chevron-left"></i> Prev</button>
                    <span>Page {{ currentPage }} of {{ totalPages }}</span>
                    <button class="btn btn-white btn-flex" @click="nextPage" :disabled="currentPage === totalPages">Next <i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
            <div v-else>
                <p>No data available to display.</p>
            </div>
        </div>
    </div>
    `,
    computed: {
        totalPages() {
            return Math.ceil(this.allAnswer / this.cardsPerPage);
        }
    },
    methods: {
        onloadShortAnswer: function (questionId, page) {
            return new Promise((resolve, reject) => {
                this.loading = true;
                vm.loading_page = true;
                vm.fetchData('get_short_answer', {
                    question_id: questionId,
                    classroom_id: this.classroom_id,
                    page: page,
                    limit: this.cardsPerPage
                }).then((response) => {
                    if (response && response.status) {
                        setTimeout(() => {
                            this.store_answer = response.data;
                            this.allAnswer = response.allAnswer; 
                            this.loading = false;
                            vm.loading_page = false;
                            resolve(true);
                        }, 1000);
                    } else {
                        this.loading = false;
                        vm.loading_page = false;
                        reject('No answer found');
                    }
                }).catch((error) => {
                    console.error(error);
                    this.loading = false;
                    vm.loading_page = false;
                    reject(error); 
                });
            });
        },
        prevPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.fetchPageData();
            }
        },
        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                this.fetchPageData();
            }
        },
        fetchPageData() {
            this.onloadShortAnswer(this.Question.question_id, this.currentPage);
        }
    },
    mounted: function () {
        this.$nextTick(async () => {
            if (this.Question?.question_id) {
                await this.onloadShortAnswer(this.Question.question_id, this.currentPage);
            }
        });
    }
});
Vue.component('option-answer-response', {
    props: {
        Question: {
            type: Object,
            required: true
        },
        classroom_id: {
            type: String
        }
    },
    data: function () {
        return {
            loading: false,
            store_options: [],
            allAnswer: 0,
            chartType: 'Pie',
        };
    },
    template: `
    <div v-loading="loading" class="el-option-answer-response" :id="'el-option-answer-response-' + Question.question_id">
        <div v-if="loading === false">
            <div class="mb-2">
                <span class="count-answer-label">{{ allAnswer }} Response</span>
            </div>
            <!-- Buttons for selecting chart type -->
            <div class="menu-container chart-type-buttons mb-3">
                <button 
                    class="btn btn-white mr-2 sub-menus" 
                    :class="{ active: chartType === 'Pie' }"
                    @click="switchChartType('Pie')">
                    <i class="fas fa-chart-pie"></i>
                </button>
                <button 
                    class="btn btn-white sub-menus" 
                    :class="{ active: chartType === 'Bar' }" 
                    @click="switchChartType('Bar')">
                    <i class="fas fa-chart-bar"></i>
                </button>
            </div>
            <div v-if="store_options.length" :id="'form_chart_' + Question.question_id"></div>
            <div v-else>
                <p>No data available to display.</p>
            </div>
        </div>
    </div>
    `,
    methods: {
        onloadOptionsAnswer: function (questionId) {
            return new Promise((resolve, reject) => {
                this.loading = true;
                vm.loading_page = true;
                vm.fetchData('get_options_answer', {
                    question_id: questionId,
                    classroom_id: this.classroom_id,
                }).then((response) => {
                    if (response && response.status) {
                        this.store_options = response.data;
                        this.allAnswer = response.countAnswer;
                        this.drawChart();
                        this.loading = false;
                        vm.loading_page = false;
                        resolve(true); 
                    } else {
                        this.loading = false;
                        vm.loading_page = false;
                        reject('No options found');
                    }
                }).catch((error) => {
                    console.error(error);
                    this.loading = false;
                    vm.loading_page = false;
                    reject(error); 
                });
            });
        },
        drawBarChart: function () {
            google.charts.load('current', { packages: ['corechart'] });
            google.charts.setOnLoadCallback(() => {
                const data = new google.visualization.DataTable();
                data.addColumn('string', 'Option');
                data.addColumn('number', 'Responses');
                this.store_options.forEach((option) => {
                    data.addRow([option.text, option.response_count || 0]);
                });
                const refKey = `form_chart_${this.Question.question_id}`;
                const el = document.getElementById(refKey);
                if (el) {
                    const dynamicHeight = 300 + (this.store_options?.length || 0 * 50);
                    el.style.height = `${dynamicHeight}px`;
                }  
                const chartHeight = (this.store_options?.length || 1) * 50;
                const options = {
                    title: 'Answer Distribution',
                    titleTextStyle: {
                        fontSize: 14,
                        bold: true,
                        color: '#777',
                    },
                    hAxis: { 
                        title: 'Responses', 
                        minValue: 0, 
                        gridlines: { color: '#e8e8e8' },
                        textStyle: { fontSize: 12 },
                        titleTextStyle: { fontSize: 14, bold: true, italic: false }
                    },
                    vAxis: { 
                        title: 'Options', 
                        textStyle: { fontSize: 12 },
                        titleTextStyle: { fontSize: 14, bold: true, italic: false }
                    },
                    bar: { groupWidth: '75%' },
                    chartArea: { 
                        width: '60%',
                        height: `${chartHeight}px`,
                        backgroundColor: '#f9f9f9', 
                    },
                    legend: { position: 'none' },
                    colors: ['#483b7e'], 
                };
                const chart = new google.visualization.BarChart(
                    document.getElementById('form_chart_' + this.Question.question_id)
                );
                chart.draw(data, options);
            });
        },
        drawPieChart: function () {
            google.charts.load('current', { packages: ['corechart'] });
            google.charts.setOnLoadCallback(() => {
                const data = new google.visualization.DataTable();
                data.addColumn('string', 'Option');
                data.addColumn('number', 'Responses');
                this.store_options.forEach((option) => {
                    data.addRow([option.text, option.response_count || 0]);
                });
                const refKey = `form_chart_${this.Question.question_id}`;
                const el = document.getElementById(refKey);
                if (el) {
                    const dynamicHeight = 300 + (this.store_options?.length || 0 * 50);
                    el.style.height = `${dynamicHeight}px`;
                }
                const options = {
                    title: 'Answer Distribution',
                    titleTextStyle: {
                        fontSize: 14,
                        bold: true,
                        color: '#777',
                    },
                    chartArea: {
                    },
                    legend: {
                        position: 'right', 
                        textStyle: { fontSize: 12 },
                    },
                    pieHole: 0.4,
                    colors: ['#4caf50', '#2196f3', '#ff9800', '#f44336', '#9c27b0'], 
                    pieSliceText: 'none', 
                };
                const chart = new google.visualization.PieChart(
                    document.getElementById('form_chart_' + this.Question.question_id)
                );
                chart.draw(data, options);
            });
        },
        drawChart: function () {
            if (this.chartType === 'Pie') {
                this.drawPieChart();
            } else {
                this.drawBarChart();
            }
        },
        switchChartType: function (type) {
            this.chartType = type; 
            this.drawChart();
        },
    },
    mounted: function () {
        this.$nextTick(async () => {
            if (this.Question?.question_id) {
                await this.onloadOptionsAnswer(this.Question.question_id);
            }
        });
    },
});
Vue.component('question-form-response', {
    props: {
        questions: {
            type: Array,
            required: true, 
        },
        classroom_id: {
            type: String,
            required: true, 
        },
    },
    data() {
        return {
            question_loading: false,
            currentQuestionIndex: 0, 
            fetchedData: [],
        };
    },
    computed: {
        currentQuestion() {
            return this.questions[this.currentQuestionIndex] || null;
        },
    },
    methods: {
        QuestionType(typeVal) {
            const types = {
                short_answer: 'Short Answer',
                multiple_choice: 'Multiple Choice',
                checkbox: 'Checkbox',
                radio: 'Radio',
            };
            return types[typeVal] || 'Unknown Type';
        },
        hasChoices(type) {
            return ['multiple_choice', 'checkbox', 'radio'].includes(type);
        },
        nextQuestion() {
            if (this.currentQuestionIndex < this.questions.length - 1) {
                this.currentQuestionIndex++;
                this.onFetchData(); 
                this.updateDropdownSelection();
            }
        },
        prevQuestion() {
            if (this.currentQuestionIndex > 0) {
                this.currentQuestionIndex--;
                this.onFetchData(); 
                this.updateDropdownSelection();
            }
        },
        onQuestionSelect(event) {
            const selectedIndex = parseInt(event.target.value);
            if (selectedIndex >= 0 && selectedIndex < this.questions.length) {
                this.currentQuestionIndex = selectedIndex;
                this.onFetchData();
            }
        },
        updateDropdownSelection() {
            const selectElement = this.$el.querySelector('select');
            if (selectElement) {
                selectElement.value = this.currentQuestionIndex;
            }
        },
        async onFetchData() {
            if (!this.classroom_id || !this.currentQuestion) return;
                this.question_loading = true;
            try {
                this.fetchedData = [];
                const response = await this.$root.fetchData('get_answer_by_question', {
                    classroom_id: this.classroom_id,
                    question_id: this.currentQuestion.question_id,
                    question_type: this.currentQuestion.type
                });
                if (response && response.status) {
                    this.fetchedData = response.data;
                }else {
                    console.error('Error: Can not fetching data.');
                }
            } catch (error) {
                console.error('Error fetching data:', error);
            } finally {
                setTimeout(() => {
                    this.question_loading = false;
                }, 300);
            }
        },
        displayUser(user) {
            const nickName = (user.nickname) ? `(${user.nickname})` : '';
            const fullName = (user.firstname) ? `${user.firstname} ${user.lastname}` : `Unknown`;
            return `${fullName} ${nickName}`;
        }
    },
    template: `
    <div>
        <div class="question-item form-container response-questions">
            <select 
            class="form-control"
            @change="onQuestionSelect"
            v-if="questions && questions.length"
            >
                <option 
                v-for="(question, index) in questions" 
                :key="index" 
                :value="index"
                >
                    {{ question.text }}
                </option>
            </select>
                <div class="pagination">
                <button 
                class="btn btn-white btn-flex" 
                @click="prevQuestion" 
                :disabled="currentQuestionIndex === 0">
                    <i class="fas fa-chevron-left"></i> Prev
                </button>
                <span class="page-info">
                    Question {{ currentQuestionIndex + 1 }} of {{ questions.length }}
                </span>
                <button 
                class="btn btn-white btn-flex" 
                @click="nextQuestion" 
                :disabled="currentQuestionIndex === questions.length - 1">
                    Next <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
        <div v-if="currentQuestion" class="selected-question">
            <div class="question-item form-container response-questions bg-purple">
                <div class="question-type">
                    <h4>{{ QuestionType(currentQuestion.type) }}</h4>
                </div>
                <div v-if="currentQuestion.type === 'checkbox'" class="desc-question-type">
                    <span>(Multiple selections allowed)</span>
                </div>
                <div v-if="currentQuestion.required">
                    <span class="text-danger">* Required</span>
                </div>
                <div v-else>
                    <span class="text-default">Not required</span>
                </div>
                <div class="question-text d-flex align-item-baseline">
                    <span class="q-label">Q:</span>
                    <div class="question-text">
                        <h4>{{ currentQuestion.text }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div v-loading="question_loading" v-if="currentQuestion && fetchedData.length" 
        v-for="(data, index) in fetchedData"
        :key="index"
        class="question-item form-container response-questions"
        >
            <div class="question-text d-flex align-item-baseline mb-1">
                <span class="a-label">A:</span>
                <div class="card-item-content a-label">
                    <span>{{ data.text }}</span>
                </div>
            </div>
            <div>
                <div v-if="data.user_info?.length > 0" class="mb-2">
                    <span class="count-answer-label">{{ data.user_info.length }} Response</span>
                </div>
                <div v-else>
                    <span class="zero-count-answer-label">0 Response</span>
                </div>
                <div v-for="(user, i) in data.user_info" 
                :key="user.user_id" class="card-item">
                    <div class="text-answer-label dotted">
                        <span>{{ displayUser(user) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    `,
    mounted() {
        this.$nextTick(() => {
            if (Array.isArray(this.questions) && this.questions.length > 0) {
                const firstQuestion = this.questions[0];
                if (firstQuestion?.question_id) {
                    this.currentQuestion.question_id = firstQuestion.question_id;
                    this.onFetchData();
                }
            }
        });
    }
});
Vue.component('question-type-modal', {
    template: `
    <div class="modal-form-type modal fade in" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Question Type</h5>
                    <button type="button" class="close" @click="closeModal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <select v-model="selectedType" class="form-control select2-exist">
                        <option value="short_answer">Short Answer</option>
                        <option value="multiple_choice">Multiple Choice</option>
                        <option value="checkbox">Checkbox</option>
                        <option value="radio">Radio</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="buttons default-button" @click="closeModal">Cancel</button>
                    <button type="button" class="buttons confirm-type-button" @click="confirmType">Confirm</button>
                </div>
            </div>
        </div>
    </div>
    `,
    data() {
        return {
            selectedType: 'short_answer' 
        };
    },
    methods: {
        confirmType() {
            this.$emit('confirm', this.selectedType);
        },
        closeModal() {
            this.$emit('close');
        }
    }
});
Vue.component('form-builder', {
    props: {
        questions: Array,
    },
    data() {
        return {
            question_loading: false,
        }
    },
    methods: {
        QuestionType(typeVal) {
            let value = '';
            switch (typeVal) {
                case 'short_answer':
                    value = 'Short Answer';
                    break;
                case 'multiple_choice':
                    value = 'Multiple Choice';
                    break;
                case 'checkbox':
                    value = 'Checkbox';
                    break;
                case 'radio':
                    value = 'Radio';
                    break;
                default:
                    break;
            }
            return value;
        },
        removeQuestion: async function(questionId, index) {
            const buttons = {
                buttons: {
                    confirm: { show: true, text: "OK", color: "#F00" },
                    cancel: { show: true, text: "Cancel", color: "#D5D5D5" },
                }
            };
            let confirm = await vm.$root.SweetAlert('Remove?', 'Are you sure you want to remove this question?', 'error', buttons);
            if (confirm) {
                this.question_loading = true;
                if (questionId) {
                    vm.fetchData('delete_question', {question_id: questionId})
                    .then((response) => {
                        if (response && response.status) {
                            vm.onMessage('success', response.data);
                            this.questions.splice(index, 1); 
                            this.question_loading = false;
                        }
                    });
                }else {
                    this.questions.splice(index, 1); 
                    this.question_loading = false;
                }
            }
        },        
        updateQuestionType(question) {
            if (!this.hasChoices(question.type)) {
                question.options = []; 
            }
        },
        hasChoices(type) {
            return ['multiple_choice', 'checkbox', 'radio'].includes(type);
        },
        onDragStart(index) {
            this.draggedQuestionIndex = index;
        },
        onDrop(index) {
            const draggedQuestion = this.questions[this.draggedQuestionIndex]; 
            this.questions.splice(this.draggedQuestionIndex, 1); 
            this.questions.splice(index, 0, draggedQuestion);
        }
    },
    template: `
    <div>
        <div class="questions-list">
            <div
                v-for="(question, index) in questions"
                :key="question.question_id"
                class="question-item move form-container questions"
                draggable="true"
                @dragstart="onDragStart(index)"
                @dragover.prevent
                @drop="onDrop(index)"
                
                v-loading="question_loading"
            >
                <div class="question-type"><h4>{{ QuestionType(question.type) }}</h4></div>
                <div v-if="question.type === 'checkbox'" class="desc-question-type"><span>(Multiple selections allowed)</span></div>
                <label class="switch">
                    <input type="checkbox" v-model="question.required" class="form-control"/>
                    <span class="slider round"></span>
                    <span class="label-text">Required</span>
                </label>
                <div class="question-text d-flex align-item-baseline">
                    <span class="q-label">Q:</span>
                    <input v-model="question.text" placeholder="Question..." class="question-text-input" maxlength="250"/>
                </div>
                <OptionsInput v-if="hasChoices(question.type)" :question="question" />
                <div class="remove-question-row">
                    <button @click="removeQuestion(question.question_id || null, index)" class="remove-question-button btn btn-danger"><i class="fas fa-trash-alt"></i> Remove</button>
                </div>
            </div>
        </div>
    </div>
    `
});
Vue.component('OptionsInput', {
    props: {
        question: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            loading: false,
            store_options: [],
            newOption: ''
        };
    },
    methods: {
        addOption() {
            const options = this.newOption.split(',').map(option => option.trim()).filter(option => option);
        
            options.forEach(option => {
                this.question.options.push({
                    choice_id: '', 
                    choice_text: option
                });
            });
            this.newOption = ''; 
        },
        onLoadOption: function() {
            return new Promise((resolve, reject) => {
                this.loading = true;
                vm.loading_page = true;
                vm.fetchData('load_options', {
                    question_id: this.question.question_id
                }).then(response => {
                    if (response && response.status) {
                        this.store_options = response.data;
                        this.question.options = [];
                        this.store_options.forEach(option => {
                            this.question.options.push({
                                choice_id: option.choice_id, 
                                choice_text: option.choice_text
                            });
                        });
                        this.loading = false;
                        vm.loading_page = false;
                        resolve(true);
                    } else {
                        this.loading = false;
                        vm.loading_page = false;
                        reject('No options found');
                    }
                }).catch(error => {
                    console.error(error);
                    reject(error);
                });
            });
        },        
        removeOption: async function(choiceId, index) {
            const buttons = {
                buttons: {
                    confirm: { show: true, text: "OK", color: "#F00" },
                    cancel: { show: true, text: "Cancel", color: "#D5D5D5" },
                }
            };
            let confirm = await vm.$root.SweetAlert('Remove?', 'Are you sure you want to remove this option?', 'error', buttons);
            if (confirm) {
                this.loading = true;
                if (choiceId) {
                    vm.fetchData('delete_choice', { choice_id: choiceId })
                        .then((response) => {
                            if (response && response.status) {
                                vm.onMessage('success', response.data);
                                this.question.options.splice(index, 1);
                                this.loading = false;
                            }
                        })
                        .catch((error) => {
                            console.error('Error removing option:', error);
                            vm.onMessage('error', 'Failed to remove option.');
                            this.loading = false;
                    });
                } else {
                    this.question.options.splice(index, 1);
                    this.loading = false;
                } 
            }
        }
    },
    template: `
    <div v-loading="loading" class="question-options">
        <hr>
        <div v-for="(option, optIndex) in question.options" :key="optIndex" class="option-item">
            <input v-model="question.options[optIndex].choice_text" class="option-input form-control" placeholder="Option" maxlength="250"/>
            <button @click="removeOption(option?.choice_id || null, optIndex)" class="remove-option-button btn btn-danger"><i class="fas fa-times"></i></button>
        </div>
        <input v-model="newOption" placeholder="Add options (comma-separated)" class="new-option-input form-control" maxlength="250"/>
        <button @click.prevent="addOption" class="add-option-button btn btn-primary">Add</button>
        <div class="other-option-checkbox">
            <input type="checkbox" class="check-box-other form-control" v-model="question.hasOtherOption" />
            <span> Include "Other" option</span>
        </div>
    </div>
    `,
    mounted: async function() {
        this.$nextTick(async () => {
            if (this.question.question_id) {
                this.loading = true
                await this.onLoadOption();
                setTimeout(() => {
                    this.loading = false
                }, 1000);
            }
        });
    }  
});
const vm = new Vue({
    el: "#classroom_form_tab",
    data() {
        return {
            classroom_id: null,
            formID: null,
            formName: '',
            questions: [],
            showModal: false,
            loading_page: false,
            loading_menu: false,
            cosent_publish: 0,
            url_destination: `/classroom/actions/forms_action.php`,
            currentTab: 'classroom-form-builder',
            formTabs: [
                { name: 'classroom-form-builder', label: 'Register Form', icon: 'fas fa-plus' },
                { name: 'classroom-form-dashboard', label: 'Response', icon: 'fas fa-check-square' },
                { name: 'classroom-form-export', label: 'Export', icon: 'fas fa-file-excel' },
            ],
            responseType: 'Summary',
            exporting: false,
            form_export: {
                excel: {
                    filter_user: '',
                    filter_date: '',
                    filter_gender: '',
                }
            }
        };
    },
    created: function() {
        this.classroom_id = this.getParameterByName('classroom_id', document.currentScript.src);
    },
    methods: {
        handleUpdateDateFilter(DateValues) {
            this.form_export.excel.filter_date = DateValues;
            this.$forceUpdate();
        },
        getData(action, data = {}, endpoint) {
            let params = '';
            for (let i in data) {
                params += `${i}=${encodeURIComponent(data[i])}&`; 
            }
            let url = `${endpoint}?action=${action}&${params}`;
            url = url.substring(0, url.length - 1);
            return fetch(url, {
                method: 'GET',
                mode: 'cors',
                headers: {
                    'Content-Type': 'application/json',
                },
            }).then(async (response) => {
                if (!response.ok) {
                    throw new Error('Failed to fetch data');
                }
                const result = await response.json();
                return result;
            }).catch((err) => {
                console.error('Error fetching data:', err); 
                throw err;
            });
        },
        exportExcel: async function() {
            try {
                if (this.formID) {
                    this.exporting = true;  
        
                    $.fileDownload("/classroom/actions/forms_action.php", {
                        httpMethod: "GET",
                        data: {
                            action: 'export_excel',
                            form_id: vm.formID,
                            date_create: vm.form_export.excel.filter_date,
                            user_id: vm.form_export.excel.filter_user,
                            gender_id: vm.form_export.excel.filter_gender,
                        }
                    }).done(() => {
                        vm.onMessage('success', 'Excel export completed successfully!');
                    }).fail((response) => {
                        let errorMessage = response.responseText || 'Export failed. Please try again.';
                        vm.onMessage('error', errorMessage);
                    });
                }
            } catch (err) {
                vm.onMessage('error', 'Error exporting Excel: ' + (err.message || err));
            } finally {
                setTimeout(() => {
                    this.exporting = false;
                }, 1500);
            }
        },            
        onFilterUser: function() {
            this.$nextTick(() => {
                $(this.$refs['export-filter-user']).select2({
                    theme: "bootstrap",
                    placeholder: "Select User",
                    minimumInputLength: 0,
                    allowClear: true,
                    ajax: {
                        url: this.url_destination,
                        dataType: 'json',
                        delay: 250,
                        cache: false,
                        data: function(params) {
                            return {
                                term: params.term || '',
                                page: params.page || 1,
                                action: 'filter_user',
                                form_id: vm.formID
                            };
                        },
                        processResults: function(data, params) {
                            let page = params.page || 1;
                            return {
                                results: $.map(data, function(item) {
                                    return {
                                        id: item.id,
                                        text: item.col,
                                        code: item.code,
                                        desc: item.desc
                                    };
                                }),
                                pagination: {
                                    more: (page * 10) <= (data[0]?.total_count || 0)
                                }
                            };
                        },
                    },
                    templateSelection: function(data) {
                        return data.text;
                    },
                }).on('select2:select', function() {
                    vm.form_export.excel.filter_user = this.value;
                    vm.$forceUpdate();
                }).on('select2:unselect', function() {
                    vm.form_export.excel.filter_user = '';
                    vm.$forceUpdate();
                });
            });
        }, 
        onFilterGender: function() {
            this.$nextTick(() => {
                $(this.$refs['export-filter-gender']).select2({
                    theme: "bootstrap",
                    placeholder: "Select Gender",
                    minimumInputLength: 0, 
                    allowClear: true,
                    ajax: {
                        url: this.url_destination,
                        dataType: 'json',
                        delay: 250,
                        cache: false,
                        data: function(params) {
                            return {
                                term: params.term || '',
                                page: params.page || 1,
                                action: 'filter_gender',
                                form_id: vm.formID
                            };
                        },
                        processResults: function(data, params) {
                            let page = params.page || 1;
                            return {
                                results: $.map(data, function(item) {
                                    return {
                                        id: item.id,
                                        text: item.col,
                                        code: item.code,
                                        desc: item.desc
                                    };
                                }),
                                pagination: {
                                    more: (page * 10) <= (data[0]?.total_count || 0)
                                }
                            };
                        },
                    },
                    templateSelection: function(data) {
                        return data.text;
                    },
                }).on('select2:select', function() {
                    vm.form_export.excel.filter_gender = this.value;
                    vm.$forceUpdate();
                }).on('select2:unselect', function() {
                    vm.form_export.excel.filter_gender = '';
                    vm.$forceUpdate();
                });
            });
        },
        switchResponseType: function(tab) {
            if(tab !== this.responseType){
                this.loading_menu = true;
                this.responseType = tab; 
                setTimeout(() => {
                    this.loading_menu = false;
                }, 1000);
            }
        },
        selectMune: function(tab) {
            if(tab !== this.currentTab){
                this.loading_menu = true;
                this.currentTab = tab; 
                if (tab === 'classroom-form-export') {
                    this.onFilterUser();
                    this.onFilterGender();
                }
                setTimeout(() => {
                    this.loading_menu = false;
                }, 1000);
            }
        }, 
        onLoadFormData: function() {
            return new Promise((resolve, reject) => {
                this.loading_page = true;
                try {
                    this.fetchData('load_form_data', {
                        classroom_id: this.classroom_id
                    })
                    .then((response) => {
                        if (response && response.status) {
                            if (response.data?.form_id) {
                                this.formID     = response.data?.form_id;
                                this.formName   = response.data?.form_name;
                                this.questions  = response.data?.questions ? response.data.questions : [];
                            }
                            this.loading_page = false;
                            resolve(true);
                        } else {
                            this.loading_page = false;
                            this.onMessage('error', 'Failed to load form data');
                            reject(new Error('Failed to load form data: Invalid response'));
                        }
                    }).catch((err) => {
                        console.error('Error loading form data:', err);
                        this.loading_page = false;
                        this.onMessage('error', 'An error occurred while loading form data');
                        reject(err);
                    });
                } catch (error) {
                    console.error('Error in onLoadFormData:', error);
                    this.loading_page = false;
                    this.onMessage('error', 'An unexpected error occurred');
                    reject(error); 
                } 
            });
        },
        mounted: async function() {
            this.$nextTick(async function() {
                this.loading_page = true;
                try {
                    if (this.classroom_id) {
                        await this.onLoadFormData();
                    } else {
                        // this.onMessage('error', 'Error', 'An error occurred while loading the form.');
                        return;
                    }
                } catch (error) {
                    console.error('Error in mounted:', error);
                } finally {
                    setTimeout(() => {
                        this.loading_page = false;
                    }, 1000);
                }
            });
        },
        addQuestion() {
            this.showModal = true; 
        },
        confirmType(selectedType) {
            this.showModal = false;
            let uniqueId;
            do {
                uniqueId = Math.floor(Math.random() * 1000000);
            } while (this.questions.some(q => q.id === uniqueId)); 
            this.questions.push({
                id: uniqueId,
                question_id: '',
                text: '',
                type: selectedType,
                required: true,
                options: [],
                hasOtherOption: false
            });
        },
        closeModal() {
            this.showModal = false; 
        }, 
        onSaveForms: async function() {
            this.loading_page = true;
            if (String(this.formName).trim() === '') {
                this.onMessage('warning', 'Please input "Form Name".');
                $(this.$refs['form-name']).focus();
                this.loading_page = false;
                return;
            }
            if (this.questions.length === 0) {
                this.onMessage('warning', 'Please add a question.');
                this.loading_page = false;
                return;
            }
            const formData = {
                classroom_id: this.classroom_id,
                formID: this.formID,
                formName: this.formName,
                questions: Array.isArray(this.questions) ? this.questions.map((question, index) => ({
                    id: question.id,
                    question_id: question.question_id,
                    text: question.text,
                    type: question.type,
                    required: question.required,
                    options: Array.isArray(question.options) ? question.options.map(option => ({
                        choice_id: option.choice_id,
                        choice_text: option.choice_text
                    })) : [],
                    hasOtherOption: question.hasOtherOption || false
                })) : [] 
            };
            try {
                const response = await this.postData('save_forms', formData);
                if (response && response.status) {
                    this.onMessage('success', response.data);
                    this.formID     = null;
                    this.formName   = '';
                    this.questions  = []
                    await this.onLoadFormData();
                    setTimeout(() => {
                        this.loading_page = false;
                    }, 1000);
                } else {
                    this.onMessage('error', 'Something went wrong');
                }
            } catch (error) {
                console.error(error);
                this.onMessage('error', 'An error occurred while saving the form');
                this.loading_page = false;
            }
        },        
        onMessage(type,title,message,duration = 2000, onClose){
            this.$notify.closeAll();
            this.$message.closeAll();
            var customClass = `notification-${type}`;
            this.$notify({
                title,
                message,
                type,
                duration,
                customClass,
                dangerouslyUseHTMLString: true,
                onClose
            });
        },
        getParameterByName(name, url) {
            name = name.replace(/[\[\]]/g, '\\$&');
            const regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)');
            const results = regex.exec(url);
            if (!results) return null;
            if (!results[2]) return '';
            return decodeURIComponent(results[2].replace(/\+/g, ' '));
        },
        redirectPost(url, data, target) {
            var form = document.createElement('form');
            document.body.appendChild(form);
            form.method = 'post';
            form.action = url;
            form.setAttribute("target", target || "_blank");
            for (var name in data) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = data[name];
                form.appendChild(input);
            }
            form.submit();
        },
        fetchData(actions, data = {}, opt = {}) {
            var end_point = opt.end_point || '/classroom/actions/forms_action.php';
            var methods = opt.methods || 'POST';
            var options = {
                method: methods,
                headers: {
                    'Content-Type': 'application/json',
                }
            };
            if(methods == 'POST'){
                options = {
                    ...options,
                    mode: 'cors',
                    body: JSON.stringify({
                        action: actions,
                        data: data
                    })
                }
            }
            return fetch(end_point,options)
            .then(async response => {
                var result = await response.json();
                return result;
            }).catch(err => {
                
            });
        },
        basePath() {
            var pathname = window.location.pathname;
            var host = window.location.host;
            var base_path = '';
            if(_.includes(['localhost','origami-dev.ap.ngrok.io.ap.ngrok.io'],host)){
                base_path = _.split(pathname,'/',2);
                base_path.shift();
                base_path = `/${base_path[0]}`;
            }
            base_path = `${window.location.protocol}//${window.location.hostname}${base_path}`;
            return base_path;
        },
        SweetCustom(title, text, icon, options = {
            buttons: {
                confirm: { text: "OK", class: "custom-confirm-button" },
                cancel: { text: "Cancel" }
            }
        }, callback) {
            const { buttons } = options;
            swal({
                title: title || '',
                text: text || '', 
                icon: icon || null,
                buttons: {
                    cancel: {
                        text: buttons.cancel?.text || "Cancel", 
                        value: false,
                        visible: true,
                        className: "custom-cancel-button",
                        closeModal: true,
                    },
                    confirm: {
                        text: buttons.confirm?.text || "OK",
                        value: true,
                        visible: true,
                        className: buttons.confirm?.class || "custom-confirm-button",
                        closeModal: true
                    }
                }
            }).then(callback);
        },
        SweetAlert: async function(title='Are you sure?', text='', icon='', 
            options = { buttons: {
                confirm: { show: false, text: "OK", color: "#FF9900" },
                cancel: { show: false, text: "Cancel", color: "#CCCCCC" }
            }}) {
            const { buttons } = options;
            return new Promise((resolve, reject) => {
                try {
                    swal({
                        html: true,
                        title: window.lang.translate(title),
                        text: text,
                        type: icon,
                        showCancelButton: buttons.confirm?.show || false,
                        closeOnConfirm: buttons.cancel?.show || false,
                        confirmButtonText: buttons.confirm?.text || 'OK',
                        cancelButtonText: buttons.cancel?.text || 'Cancel',	
                        confirmButtonColor: buttons.confirm?.color || '#FF9900',
                        cancelButtonColor: buttons.cancel?.color || '#CCCCCC',
                        showLoaderOnConfirm: true,
                    },
                    function(isConfirm){
                        resolve((isConfirm) ? true : false);
                    });
                } catch (error) {
                    reject(`Error: ${error}`);
                } 
            });
        },
        postData: async function (action, data = {}, opt = {}) {
            var formData = new FormData();
            formData.append('action', action);
            for (var i in data) {
                if (typeof data[i] == 'object' && data[i] !== null) {
                    if (Array.isArray(data[i])) {
                        data[i].forEach((item, count_index) => {
                            if (typeof item === 'object') {
                                for (var x in item) {
                                    if (Array.isArray(item[x])) {
                                        for (var o in item[x]) {
                                            this.postObject(formData, `${i}[${count_index}][${x}][${o}]`, item[x][o] || '');
                                        }
                                    }else {
                                        formData.append(`${i}[${count_index}][${x}]`, item[x] || '');
                                    }
                                }
                            } else {
                                formData.append(`${i}[${count_index}]`, item || '');
                            }
                        });
                    } else {
                        this.postObject(formData, i, data[i] || '');
                    }
                } else {
                    formData.append(i, data[i] || '');
                }
            }
            try {
                var response = await axios.post(opt.end_point || this.url_destination, formData);
                if (response.status == 200) {
                    return response.data;
                }
            } catch (err) {
                let status = response.status || false;
                let statusText = response.statusText || err.message;
                return {
                    status: false,
                    error_code: status,
                    error_msg: statusText
                };
            }
            return {
                status: false
            }; 
        },
        postObject(formData, base_key, base_data) {
            _.forIn(base_data, (value, key) => {
                if (typeof value == "object" && value !== null) {
                    this.postObject(formData, `${base_key}[${key}]`, value);
                } else {
                    formData.append(`${base_key}[${key}]`, value || '');
                }
            });
        }        
    },
    mounted: async function() {
        this.$nextTick(async function() {
            this.loading_page = true
            if (this.classroom_id) {
                await this.onLoadFormData();
            }else {
                // this.onMessage('error', 'Error', 'An error occurred while loading the form.');
                return;
            }
            setTimeout(() => {
                this.loading_page = false
            }, 1000);
        });
    }
});