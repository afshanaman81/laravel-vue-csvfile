<template>
    <div id="view-data" class="container" >
        <div class="row">
            <div class="col-md-12">

                <br>
                <table class="table table-striped text-left">
                    <thead>
                    <tr>
                        <th v-for="(value, key) in headerData" 
                            @click="sort(key)"
                            scope="col" class="text-capitalize">
                            <a href="#">{{ value}} <span class="fa fa-sort"></span></a>
                        </th>                    
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(row,index) in sortedData">
                        <td v-for="(value,key) in row" v-if="key != '_id'">{{ value }}</td>                        
                    </tr>
                    </tbody>

                </table>
            </div>
        </div>

        <div class="row form-group">
            <div class="col-md-12">
                <div id="pagination">
                    <span>{{ pageInfo }}</span>
                    <span @click="prevPage" class="fa fa-angle-double-left" title="previous page"></span>
                    <span @click="nextPage" class="fa fa-angle-double-right" title="next page"></span>
                </div>
            </div>
        </div>

        <div class="row">

            <div class="col-md-12"> 
                <hr />
                <h3>Add New column</h3> 
                <div class="form-group text-left">
                    <label for="column">New Column Name</label>
                    <input type="text" id="column" class="form-control"
                            v-model="newColumn">
                </div>

                <div class="form-group text-left">
                    <div class="row form-group">
                        <div class="col-md-12">Structured Formula</div>
                    </div>
                    <div class="row form-group">
                        <div class="col-md-5">
                            <label for="column-one">Column 1</label>
                            <select id="column-one" class="form-control"
                                v-model="formula.columnOne" 
                                :disabled="formula.unStructured !== ''">
                                <option value=""></option>
                                <option v-for="column in NumericColumns" 
                                    :value="column" >{{ column }}</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="operator">Operator</label>                        
                            <select id="operator" class="form-control"
                                v-model="formula.operator" 
                                :disabled="formula.unStructured !== ''">
                                <option v-for="op in operators" 
                                    :value="op" >{{ op }}</option>
                            </select>
                        </div>

                        <div class="col-md-5">
                            <label for="column-two">Column 2</label>
                            <select id="column-two" class="form-control"
                                v-model="formula.columnTwo" 
                                :disabled="formula.unStructured !== ''">
                                <option value=""></option>
                                <option v-for="column in NumericColumns" 
                                    :value="column" >{{ column }}</option>
                            </select>
                        </div>
                    </div>
                </div> 

                <div class="form-group text-left">
                    <div class="row form-group">
                        <div class="col-md-12">Un-structured Formula</div>
                    </div>
                    <div class="row form-group">
                        <div class="col-md-12">
                            <label for="column-unstructured">Statement</label>
                            <input type="text" id="column-unstructured" class="form-control"
                                :disabled="formula.columnOne !== ''" 
                                v-model="formula.unStructured">
                        </div>
                    </div>
                </div>
                
                <div class="form-group text-center">
                    <input v-on:click="addNewColumn" value="Add" 
                            class="btn btn-warning uniform-width">
                    <input v-on:click="clearForm" value="Clear Form" 
                            class="btn btn-success uniform-width">
                </div>            
            </div>            
        </div>
    </div>
</template>

<script>

    export default {
        props:['data'],
        data(){
            return {
                pageSize: 5,
                currentSort:'name',
                currentSortDir:'asc',
                pageInfo: '',
                currentPage:1,
                headerData: [],
                bodyData:[],
                NumericColumns:[],
                operators: ['+', '*', '-', '/'],
                newColumn: 'Ss',
                formula: {
                    columnOne: '',
                    columnTwo: '',
                    operator: '',
                    unStructured:'product & " sales in " & city & " were " & sales'
                },
            }
        },
        mounted() {
            console.log('Component mounted.')
            this.parseData()   
            this.getNumericColumns()     
            this.getPageInfo()
        },
        methods:{
            parseData: function(){
                this.headerData = this.data[0].map(element =>{
                    return element.toLowerCase()
                })
               
                this.data.forEach((row, rowIndex) =>{
                    //console.log(row)
                    if (rowIndex > 0){
                        let newJson = {}
                        row.forEach((value, colIndex) => {                                                    
                            newJson[this.headerData[colIndex]] = value                            
                        })

                        //console.log(newJson)
                        this.bodyData.push(newJson)
                    }                                       
                })
                //console.log(this.bodyData)
            },
            getNumericColumns: function(){
                let row = this.data[1]
                row.forEach((cell, index) =>{            
                    //console.log([cell, typeof(cell)])  
                    if (cell == Number(cell)){
                        //console.log(`${cell} is a number`)
                        this.NumericColumns.push(this.headerData[index])
                    }
                })
                //console.log(this.NumericColumns)
            },
            addNewColumn: function(){
                // error handling
                // 1. Column Name Check            
                if (this.newColumn === ''){
                    this.$toastr.e("Must provide name for new column"); 
                    return
                }
                // 2. Column Duplicate Name Check
                if (!this.headerData.includes(this.newColumn))
                    this.headerData.push(this.newColumn.toLowerCase())
                else{
                    this.$toastr.i("Column Already Exists");                
                }


                if (this.formula.columnOne !== '')
                    this.evalStructured()
                else
                    this.evalUnstructured()

            },
            evalStructured: function(){
                this.bodyData.forEach(row => {
                    const op1 = (row[this.formula.columnOne]== Number(row[this.formula.columnOne]))?row[this.formula.columnOne]:0
                    const op2 = (row[this.formula.columnTwo]== Number(row[this.formula.columnTwo]))?row[this.formula.columnTwo]:0
                    row[this.newColumn.toLowerCase()] = eval(`${op1} ${this.formula.operator} ${op2}`)
                })    
                //console.log(this.bodyData)
            },
            evalUnstructured: function(){
                const statementArray = [].concat.apply([], this.formula.unStructured.split('"').map(function(v,i){
                   return i%2 ? v : v.split(' ')
                })).filter(Boolean);            
                //https://stackoverflow.com/questions/18703669/split-string-but-not-words-inside-quotation-marks
               
                this.bodyData.forEach(row => {
                    let statementToEval = ``
                    statementArray.forEach(element => {
                       
                        if (element === '&'){
                            //console.log(`${element} is a space`)
                            statementToEval += ""
                        }else if (this.operators.includes(element)){
                            //console.log(`${element} is an operator`)
                            statementToEval += element
                        }else if (this.headerData.includes(element)){
                            //console.log(`${element} is a column`)
                            statementToEval += `${row[element]}`
                        }else{
                            //console.log(`${element} is a literal`)
                            statementToEval += `${element}`
                        }
                    })                
                    row[this.newColumn.toLowerCase()] = statementToEval
                })
            },
            clearForm: function(){
                this.formula = {
                    columnOne: '',
                    columnTwo: '',
                    operator: '',
                    unStructured:''
                }
                this.newColumn = ''
            },
            sort:function(s) {
                //toggle search
                if(s === this.currentSort) {
                    this.currentSortDir = this.currentSortDir==='asc'?'desc':'asc';
                }
                this.currentSort = s
            },
            nextPage:function() {
                if((this.currentPage*this.pageSize) < this.bodyData.length) this.currentPage++;
                this.getPageInfo()
            },
            prevPage:function() {
                if(this.currentPage > 1) this.currentPage--;
                this.getPageInfo()
            },
            getPageInfo:function(){
                const start = (this.currentPage -1) * this.pageSize +1
                const end = (this.currentPage * this.pageSize < this.bodyData.length)? this.currentPage * this.pageSize: this.bodyData.length
                this.pageInfo = `(showing ${start} - ${end} of ${this.bodyData.length})`
            }
        },
        computed:{
            sortedData: function(){
                let modifier = 1
                return this.bodyData.sort((a, b) =>{
                    if(this.currentSortDir === 'desc') modifier = -1
                    if(a[this.currentSort] < b[this.currentSort]) return -1 * modifier
                    if(a[this.currentSort] > b[this.currentSort]) return  1 * modifier
                    return 0
                }).filter((row, index) => {
                    let start = (this.currentPage-1)*this.pageSize;
                    let end = this.currentPage*this.pageSize;
                    if(index >= start && index < end) return true;
                })
            }
        }
    }
</script>

<style scoped>
    #pagination{
        text-align: right;
        border: 1px dotted;
    }
    #pagination span{
        margin-right: 2vw;
    }
    #queryStatus{
        float: left;
        text-align: left;
    }
    .float-right{
        float: right;
        text-align: right;
    }
    .btn-actions{font-size: 16px;}
    #dialog-buttons span{
        margin-left: 1.5vw;
    }
    .style{
        position: relative;
        display: block;
        border: 1px solid;
        margin: 0 auto;
        padding: 1vh 2vw;
        color: red;
        background: whitesmoke;
        font-size: smaller;
    }
    #operator,
    #operator option{
        font-size: 24px;
    }
    

</style>