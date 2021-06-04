<template>
  <div class="dashboard-editor-container">
    <!-- <el-card>
      <el-tabs>
        <el-carousel :interval="6000" type="card" height="800px">
          <el-carousel-item v-for="item in carouselImages" :key="item">
            <img :src="item" class="image">
          </el-carousel-item>
        </el-carousel>
      </el-tabs>
    </el-card> -->
    <!-- <github-corner style="position: absolute; top: 0px; border: 0; right: 0;" /> -->

    <panel-group @handleSetLineChartData="handleSetLineChartData" v-loading="loading"/>

    <el-row style="background:#fff;padding:32px 32px 0;margin-bottom:64px;">
      <line-chart :chart-data="lineChartData" />
    </el-row>

    <!-- <el-row :gutter="32">
      <el-col :xs="24" :sm="24" :lg="8">
        <div class="chart-wrapper">
          <raddar-chart />
        </div>
      </el-col>
      <el-col :xs="24" :sm="24" :lg="8">
        <div class="chart-wrapper">
          <pie-chart />
        </div>
      </el-col>
      <el-col :xs="24" :sm="24" :lg="8">
        <div class="chart-wrapper">
          <bar-chart />
        </div>
      </el-col>
    </el-row> -->

    <el-row :gutter="8">
      <el-col :xs="{span: 24}" :sm="{span: 24}" :md="{span: 24}" :lg="{span: 12}" :xl="{span: 12}" style="padding-right:8px;margin-bottom:30px;">
        <transaction-table />
      </el-col>
      <el-col :xs="{span: 24}" :sm="{span: 12}" :md="{span: 12}" :lg="{span: 6}" :xl="{span: 6}" style="margin-bottom:30px;">
        <todo-list />
      </el-col>
      <el-col :xs="{span: 24}" :sm="{span: 12}" :md="{span: 12}" :lg="{span: 6}" :xl="{span: 6}" style="margin-bottom:30px;">
        <box-card />
      </el-col>
    </el-row>
  </div>
</template>

<script>
import GithubCorner from '@/components/GithubCorner';
import PanelGroup from './components/PanelGroup';
import LineChart from './components/LineChart';
import RaddarChart from './components/RaddarChart';
import PieChart from './components/PieChart';
import BarChart from './components/BarChart';
import TransactionTable from './components/TransactionTable';
import TodoList from './components/TodoList';
import BoxCard from './components/BoxCard';
import { fetchErrors } from '@/api/servers';
// const lineChartData = {
//   newVisitis: {
//     expectedData: [0, 0, 0, 0, 105, 160, 200],
//   },
// };
const logo = require('@/assets/login/hamkor_logo.png');
export default {
  name: 'DashboardAdmin',
  components: {
    GithubCorner,
    PanelGroup,
    LineChart,
    RaddarChart,
    PieChart,
    BarChart,
    TransactionTable,
    TodoList,
    BoxCard,
  },
    created() {
    this.handleSetLineChartData(this.typeDef);

  },
  data() {
    return {
      lineChartData: {},
      loading: true,
      typeDef:'Shina',
      list: {},
      // carouselImages: [
      //   {
      //     src: '.../../../assets/login/hamkor.png'
      //   },
      // ],
      carouselImages: [
        'https://cdn.laravue.dev/photo1.png',
        'https://cdn.laravue.dev/photo2.png',
        'https://cdn.laravue.dev/photo3.jpg',
        'https://cdn.laravue.dev/photo4.jpg',

      ],
    };
  },
  methods: {
    async handleSetLineChartData(type) {
      this.loading = true;  
     await fetchErrors(type).then(response => {
      this.list = {
          type: {
            expectedData: response.data.expectedData,
          },
        };
        this.lineChartData = this.list.type;
     
      });
      this.loading = false;
    }
  },
};
</script>

<style rel="stylesheet/scss" lang="scss" scoped>
.dashboard-editor-container {
  padding: 32px;
  background-color: rgb(240, 242, 245);
  .chart-wrapper {
    background: #fff;
    padding: 16px 16px 0;
    margin-bottom: 32px;
  }
}
</style>
